<?php
declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\BuilderStructureRevision as Revision;

it('rejects stale writes through the legacy save structure action', function() {
    $menu = F::menu();
    $a = F::customNode($menu, 'Audit A', '/a');
    $b = F::customNode($menu, 'Audit B', '/b');
    $revision = Revision::get($menu);
    $moves = static fn($first, $second) => [
        ['elementId' => $first->id, 'parentId' => null, 'prevId' => null],
        ['elementId' => $second->id, 'parentId' => null, 'prevId' => $first->id],
    ];
    Revision::apply($menu, $revision, fn() => N::$plugin->getBuildSessions()->applyStructureMoves($menu, $a->siteId, $moves($b, $a)));
    boundaryRequest(function() use ($menu, $a, $b, $revision, $moves) {
        Craft::$app->request->setBodyParams(['menuId' => $menu->id, 'siteId' => $a->siteId,
            'applyStructure' => true, 'moves' => $moves($a, $b), 'structureRevision' => $revision]);
        expect(fn() => (new verbb\navigation\controllers\NodesController('nodes', N::$plugin))->actionSaveStructure())
            ->toThrow(yii\web\ConflictHttpException::class);
        $order = Node::find()->menuId($menu->id)->ids();
        expect($order)->toBe([$b->id, $a->id]);
        // An empty posted tree must not fall back to unversioned saved session moves.
        Craft::$app->request->setBodyParams(['menuId' => $menu->id, 'siteId' => $a->siteId,
            'applyStructure' => true, 'moves' => []]);
        expect(fn() => (new verbb\navigation\controllers\NodesController('nodes', N::$plugin))->actionSaveStructure())
            ->toThrow(yii\web\BadRequestHttpException::class);
        expect(Node::find()->menuId($menu->id)->ids())->toBe([$b->id, $a->id]);
    });
});

it('prevents another editor enabling a private pending add', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Private pending node', '/private');
    $node->setPendingPublish(true);
    $node->enabled = false;
    $node->setEnabledForSite(false);
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    $owner = new craft\elements\User(['username' => 'audit-owner-' . uniqid(), 'email' => uniqid('audit-') . '@example.test']);
    expect(Craft::$app->elements->saveElement($owner))->toBeTrue();
    $sessions = N::$plugin->getBuildSessions();
    $session = $sessions->getOrCreate($menu->id, $node->siteId, $owner->id);
    $sessions->stageAddedNodes($session, [$node->id]);
    boundaryRequest(function() use ($menu, $node, $owner) {
        $editor = new craft\elements\User(['username' => 'audit-editor-' . uniqid(), 'email' => uniqid('editor-') . '@example.test']);
        expect(Craft::$app->elements->saveElement($editor))->toBeTrue();
        Craft::$app->set('userPermissions', new craft\services\UserPermissions());
        Craft::$app->userPermissions->saveUserPermissions($editor->id, ['accessCp', 'navigation-manageMenu:' . $menu->uid]);
        Craft::$app->user->setIdentity($editor);
        expect($editor->admin)->toBeFalse();
        expect($editor->can('navigation-manageMenu:' . $menu->uid))->toBeTrue();
        expect(Craft::$app->user->id)->not->toBe($owner->id);
        Craft::$app->request->setBodyParams(['menuId' => $menu->id, 'siteId' => $node->siteId,
            'nodeIds' => [$node->id], 'status' => 'enabled']);
        expect(fn() => (new verbb\navigation\controllers\BuilderController('builder', N::$plugin))->actionSetNodeStatus())
            ->toThrow(yii\web\ForbiddenHttpException::class);
        $public = Node::find()->id($node->id)->exists();
        expect($node->canSave($editor))->toBeFalse();
        $node->clearPendingPublish();
        expect($node->canSave($editor))->toBeFalse(); // Posted flags cannot bypass stored ownership.
        expect($public)->toBeFalse();
    });
});

it('preserves a FreeNav node disabled for its source site', function() {
    Tests\Support\Fixtures\PluginMigrationFixture::with('free-nav', function($f) {
        Craft::$app->db->createCommand()->update('{{%elements_sites}}', ['enabled' => false],
            ['elementId' => $f['parent']->id, 'siteId' => $f['primary']->id])->execute();
        $class = N::$plugin->getMigrations()->getMigratorClass('free-nav');
        $result = N::$plugin->createMigrator($class, ['handle' => $f['handle']])->run();
        expect($result->ok)->toBeTrue();
        $menu = N::$plugin->getMenus()->getMenuByHandle($f['handle']);
        $node = Node::find()->menuId($menu->id)->siteId($f['primary']->id)->status(null)->one();
        expect($node->getEnabledForSite())->toBeFalse();
    });
});

it('rejects import completion through GET before replacing content', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Original', '/original');
    $payload = verbb\navigation\helpers\ImportExportHelper::generateMenuExport($menu);
    $payload['nodes'][0]['title'] = 'GET replacement';
    boundaryRequest(function() use ($menu, $node, $payload) {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $filename = 'navigation-import-991231_235959.json';
        $path = Craft::$app->path->getTempPath() . '/' . $filename;
        file_put_contents($path, json_encode($payload));
        Craft::$app->request->setQueryParams(['filename' => $filename, 'menuAction' => 'update']);
        Craft::$app->request->setBodyParams([]);
        $error = null;
        try {
            (new verbb\navigation\controllers\ImportExportController('import-export', N::$plugin))->runAction('import-complete');
        } catch (Throwable $e) {
            $error = get_class($e) . ': ' . $e->getMessage();
        } finally {
            unlink($path);
        }
        $originalExists = Node::find()->id($node->id)->exists();
        expect($error)->toStartWith(yii\web\MethodNotAllowedHttpException::class);
        expect($originalExists)->toBeTrue();
    });
});

it('rolls back replacement when an existing node refuses deletion', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Original kept by extension', '/original');
    $payload = verbb\navigation\helpers\ImportExportHelper::generateMenuExport($menu);
    $payload['nodes'][0]['title'] = 'Replacement';
    $veto = static function($event) use ($node) {
        if ($event->sender->id === $node->id) $event->isValid = false;
    };
    yii\base\Event::on(Node::class, Node::EVENT_BEFORE_DELETE, $veto);
    try {
        $result = verbb\navigation\helpers\ImportExportHelper::importMenuFromJson($payload, 'update');
    } finally {
        yii\base\Event::off(Node::class, Node::EVENT_BEFORE_DELETE, $veto);
    }
    $titles = array_column(Node::find()->menuId($menu->id)->all(), 'title');
    expect($result->hasImportErrors())->toBeTrue();
    expect($titles)->toBe(['Original kept by extension']);
});


it('keeps a manually published session addition when the owner discards', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Manual publish', '/manual');
    $node->setPendingPublish(true);
    $node->enabled = false;
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    boundaryRequest(function() use ($menu, $node) {
        $sessions = N::$plugin->getBuildSessions();
        $session = $sessions->getOrCreate($menu->id, $node->siteId);
        $sessions->stageAddedNodes($session, [$node->id]);
        expect($node->canSave(Craft::$app->user->identity))->toBeTrue();
        $node->enabled = true;
        $node->setEnabledForSite(true);
        expect(Craft::$app->elements->saveElement($node))->toBeTrue();
        expect($sessions->getSession($menu->id, $node->siteId)->addedNodeIds)->toBe([]);
        expect(fn() => $sessions->discard($session))->toThrow(yii\web\ConflictHttpException::class);
        $sessions->discard($sessions->getSession($menu->id, $node->siteId));
        expect(Node::find()->id($node->id)->exists())->toBeTrue();
    });
});

it('clears only numeric legacy node slugs without database-specific operators', function() {
    $menu = F::menu();
    $numeric = F::customNode($menu, 'Numeric', '/numeric');
    $text = F::customNode($menu, 'Text', '/text');
    $empty = F::customNode($menu, 'Empty', '/empty');
    foreach ([[$numeric, '123'], [$text, 'node-123'], [$empty, '']] as [$node, $slug]) {
        Craft::$app->db->createCommand()->update('{{%elements_sites}}', ['slug' => $slug], ['elementId' => $node->id])->execute();
    }
    $migration = new verbb\navigation\migrations\m260627_000000_nodes_sites_and_menu_elements();
    (new ReflectionMethod($migration, '_clearNodeSlugHack'))->invoke($migration);
    $slug = fn($node) => (new craft\db\Query())->select('slug')->from('{{%elements_sites}}')->where(['elementId' => $node->id])->scalar();
    expect($slug($numeric))->toBeNull();
    expect($slug($text))->toBe('node-123');
    expect($slug($empty))->toBe('');
});


it('returns the actual before and after structure revisions for builder duplication', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Duplicate source', '/source');
    $before = Revision::get($menu);
    boundaryRequest(function() use ($menu, $node, $before) {
        Craft::$app->request->setBodyParams(['menuId' => $menu->id, 'siteId' => $node->siteId, 'nodeIds' => [$node->id]]);
        $response = (new verbb\navigation\controllers\BuilderController('builder', N::$plugin))->actionDuplicateNodes();
        expect($response->statusCode)->toBe(200);
        expect($response->data['previousStructureRevision'])->toBe($before);
        expect($response->data['structureRevision'])->toBe(Revision::get($menu))->not->toBe($before);
        expect($response->data['duplicatedNodeIds'])->toHaveCount(1);
    });
});

it('still completes a valid import from POST body parameters', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Before POST', '/before');
    $payload = verbb\navigation\helpers\ImportExportHelper::generateMenuExport($menu);
    $payload['nodes'][0]['title'] = 'After POST';
    boundaryRequest(function() use ($menu, $node, $payload) {
        $filename = 'navigation-import-991231_235958.json';
        $path = Craft::$app->path->getTempPath() . '/' . $filename;
        file_put_contents($path, json_encode($payload));
        Craft::$app->request->setBodyParams(['filename' => $filename, 'menuAction' => 'update']);
        // Only replace session/redirect presentation, unavailable in a console app.
        $controller = new class('import-export', N::$plugin) extends verbb\navigation\controllers\ImportExportController {
            public function setSuccessFlash(?string $default = null, array $settings = []): void {}
            public function redirectToPostedUrl(?object $object = null, ?string $default = null): yii\web\Response {
                return $this->asJson(['success' => true]);
            }
        };
        try {
            expect($controller->actionImportComplete()->statusCode)->toBe(200);
            expect(Node::find()->id($node->id)->exists())->toBeFalse();
            expect(Node::find()->menuId($menu->id)->one()->title)->toBe('After POST');
        } finally {
            unlink($path);
        }
    });
});
