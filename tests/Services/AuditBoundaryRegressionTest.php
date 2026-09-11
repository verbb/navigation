<?php
declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\ImportExportHelper as Import;
use verbb\navigation\helpers\MenuPermissionMigration;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\nodetypes\Custom;

function boundaryRequest(callable $callback): mixed
{
    return W::withAbsoluteUrl('https://boundary.invalid/', function() use ($callback) {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Craft::$app->request->headers->set('Accept', 'application/json');
        $user = Craft::$app->getUser();
        $identity = $user->getIdentity();
        $response = Craft::$app->getResponse();
        $sources = Craft::$app->getElementSources();
        $sourceProperty = new ReflectionProperty(craft\base\Element::class, 'sources');
        $nativeSources = $sourceProperty->getValue();
        $sourceProperty->setValue(null, []);
        // Sources are request-cached by Craft. Each simulated request must see
        // sections and the identity created since previous tests ran.
        Craft::$app->set('elementSources', new craft\services\ElementSources());
        $user->setIdentity(craft\elements\User::find()->admin()->one());
        Craft::$app->set('response', new craft\web\Response());
        try {
            return $callback();
        } finally {
            $user->setIdentity($identity);
            Craft::$app->set('response', $response);
            Craft::$app->set('elementSources', $sources);
            $sourceProperty->setValue(null, $nativeSources);
        }
    });
}

function boundaryAddPayload($menu, string $title): array
{
    return ['menuId' => $menu->id, 'siteId' => Craft::$app->sites->getPrimarySite()->id,
        'title' => $title, 'url' => '/boundary', 'type' => Custom::class];
}

it('validates the final author URL after suffix composition and control normalization', function() {
    $menu = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    $node = F::customNode($menu, 'Boundary', '/safe');
    foreach ([['', 'javascript:0'], ['java', 'script:0'], ["java\tscript:0", ''], ['data:text/plain,inert', '']] as [$base, $suffix]) {
        $node->setUrl($base);
        $node->urlSuffix = $suffix;
        expect($node->getUrl())->toBeNull();
        expect((string)$node->getLink())->not->toContain('href=');
    }
    $node->setUrl('/safe'); $node->urlSuffix = '?page=2';
    expect($node->getUrl())->toBe('/safe?page=2');
});

it('bypasses unsupported cache shapes and preserves supported cold warm identity', function() {
    $menu = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    $a = F::customNode($menu, 'Alpha', '/alpha');
    F::customNode($menu, 'Beta', '/beta');
    W::withAbsoluteUrl('https://boundary.invalid/', function() use ($menu, $a) {
        $cache = N::$plugin->getNavigationCache();
        $query = fn() => Node::find()->menuId($menu->id)->withNodeHierarchy(false);
        expect($cache->shouldCacheQuery($query()))->toBeTrue();
        $cold = $query()->all();
        $warm = $query()->all();
        expect($cache->getCachedNodes($query()))->not->toBeNull();
        expect(array_column($warm, 'uid'))->toBe(array_column($cold, 'uid'));
        $descending = $query()->orderBy(['elements_sites.title' => SORT_DESC]);
        expect($cache->shouldCacheQuery($descending))->toBeFalse();
        expect(array_column($descending->all(), 'title'))->toBe(['Beta', 'Alpha']);
        expect(array_column($query()->andWhere(['elements.id' => $a->id])->all(), 'title'))->toBe(['Alpha']);
        expect($cache->shouldCacheQuery($query()->site('*')))->toBeFalse();
        expect($cache->shouldCacheQuery($query()->menuId([$menu->id])))->toBeFalse();
        expect($cache->shouldCacheQuery($query()->includePendingProjections()))->toBeFalse();
        $a->setElementSiteId(F::existingSecondarySite()->id);
        $copy = $cache->hydrateNodes($cache->exportNodes([$a], $cache::PROFILE_STANDARD))[0];
        expect($copy->getElementSiteId())->toBe($a->getElementSiteId());
    });
});

it('rejects a menu grant without the posted site grant before creating nodes', function() {
    // Site grants apply in multisite; the canonical runner prunes sites per test.
    F::secondarySite();
    $menu = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    boundaryRequest(function() use ($menu) {
        $identity = new class extends craft\elements\User {
            public array $grants = [];
            public function can(string $permission): bool { return in_array($permission, $this->grants, true); }
        };
        $identity->id = Craft::$app->getUser()->id;
        $identity->grants = ['navigation-manageMenu:' . $menu->uid];
        Craft::$app->getUser()->setIdentity($identity);
        Craft::$app->request->setBodyParams(['nodes' => [boundaryAddPayload($menu, 'Denied')]]);
        expect(fn() => (new verbb\navigation\controllers\NodesController('nodes', N::$plugin))->actionAddNodes())
            ->toThrow(yii\web\ForbiddenHttpException::class);
        expect((int)Node::find()->menuId($menu->id)->status(null)->count())->toBe(0);
    });
});

it('intersects linked source restrictions with picker settings and rejects disabled types', function() {
    $menu = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    $allowedSection = F::entrySection();
    $allowed = F::entryNode($menu, F::entries(1, $allowedSection)[0]);
    $denied = F::entryNode($menu, F::entries(1)[0]);
    $single = F::homepageEntry()->getSection();
    $type = verbb\navigation\nodetypes\Entry::class;
    $menu->permissions = [$type => ['enabled' => true, 'permissions' => ['section:' . $allowedSection->uid],
        'hideSingleSectionUids' => [$single->uid]]];
    expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    boundaryRequest(function() use ($menu, $allowed, $denied, $type) {
        $user = Craft::$app->getUser()->getIdentity();
        expect($allowed->canSave($user))->toBeTrue();
        expect($denied->canSave($user))->toBeFalse();
        $menu->permissions[$type]['enabled'] = false;
        expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
        expect($allowed->canSave($user))->toBeFalse();
    });
});

it('rolls back earlier batch members when later validation fails', function() {
    $menu = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    boundaryRequest(function() use ($menu) {
        Craft::$app->request->setBodyParams(['nodes' => [boundaryAddPayload($menu, 'Valid'), boundaryAddPayload($menu, str_repeat('x', 300))]]);
        $response = (new verbb\navigation\controllers\NodesController('nodes', N::$plugin))->actionAddNodes();
        expect($response->statusCode)->toBe(400);
        expect($response->data['errors'])->toHaveKey('title');
        expect((int)Node::find()->menuId($menu->id)->status(null)->count())->toBe(0);
        expect(N::$plugin->getBuildSessions()->getSession($menu->id, Craft::$app->sites->getPrimarySite()->id))->toBeNull();
    });
});

it('rolls back node writes when session persistence fails', function() {
    $menu = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    boundaryRequest(function() use ($menu) {
        $fail = static function($event) { $event->isValid = false; };
        yii\base\Event::on(verbb\navigation\records\BuildSession::class, 'beforeInsert', $fail);
        try {
            Craft::$app->request->setBodyParams(['nodes' => [boundaryAddPayload($menu, 'Valid')]]);
            expect(fn() => (new verbb\navigation\controllers\NodesController('nodes', N::$plugin))->actionAddNodes())->toThrow(yii\base\UserException::class);
        } finally {
            yii\base\Event::off(verbb\navigation\records\BuildSession::class, 'beforeInsert', $fail);
        }
        expect((int)Node::find()->menuId($menu->id)->status(null)->count())->toBe(0);
    });
});

it('restores menu config and nested content after late import failure', function() {
    $menu = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    $parent = F::customNode($menu, 'Parent', '/parent');
    $child = F::customNode($menu, 'Child', '/child', $parent);
    $before = Craft::$app->projectConfig->get('navigation.menus.' . $menu->uid);
    $payload = Import::generateMenuExport($menu);
    $payload['menu']['name'] = 'Rejected rename';
    $payload['nodes'] = [['type' => Custom::class, 'title' => str_repeat('x', 300), 'url' => '/invalid']];
    $result = Import::importMenuFromJson($payload, 'update');
    expect($result->hasImportErrors())->toBeTrue();
    expect(N::$plugin->getMenus()->getMenuById($menu->id)->name)->toBe($menu->name);
    expect(Craft::$app->projectConfig->get('navigation.menus.' . $menu->uid))->toBe($before);
    expect(Node::find()->id($child->id)->one()?->getParent()?->id)->toBe($parent->id);
});

it('does not create a menu when an import fails after preflight', function() {
    $menu = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    $payload = Import::generateMenuExport($menu);
    $payload['menu']['handle'] = 'failedImport' . uniqid();
    $payload['nodes'] = [['type' => Custom::class, 'title' => str_repeat('x', 300), 'url' => '/invalid']];
    $result = Import::importMenuFromJson($payload);
    expect($result->hasImportErrors())->toBeTrue();
    expect(N::$plugin->getMenus()->getMenuByHandle($payload['menu']['handle']))->toBeNull();
});

it('renames lowercase permissions and merges colliding user assignments idempotently', function() {
    $transaction = Craft::$app->db->beginTransaction();
    try {
        $suffix = craft\helpers\StringHelper::UUID();
        $old = new craft\records\UserPermission(['name' => 'navigation-managenav:' . $suffix]); $old->save();
        $new = new craft\records\UserPermission(['name' => 'navigation-managemenu:' . $suffix]); $new->save();
        $userId = craft\elements\User::find()->admin()->one()->id;
        craft\helpers\Db::insert('{{%userpermissions_users}}', ['permissionId' => $old->id, 'userId' => $userId]);
        craft\helpers\Db::insert('{{%userpermissions_users}}', ['permissionId' => $new->id, 'userId' => $userId]);
        MenuPermissionMigration::migrate();
        MenuPermissionMigration::migrate();
        expect(craft\records\UserPermission::findOne($old->id))->toBeNull();
        expect((int)(new craft\db\Query())->from('{{%userpermissions_users}}')->where(['permissionId' => $new->id, 'userId' => $userId])->count())->toBe(1);
    } finally { $transaction->rollBack(); }
});

it('does not treat another session pending add or delete as an orphan', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Private change', '/private');
    $secondary = F::existingSecondarySite();
    $userId = craft\elements\User::find()->admin()->one()->id;
    $sessions = N::$plugin->getBuildSessions();
    $owner = $sessions->getOrCreate($menu->id, $node->siteId, $userId);
    $other = $sessions->getOrCreate($menu->id, $secondary->id, $userId);
    $sessions->stageDelete($owner, $node);
    $variant = Node::find()->id($node->id)->siteId($secondary->id)->status(null)->one();
    expect(fn() => $sessions->unstageDelete($other, $variant))->toThrow(yii\base\UserException::class);
    expect($sessions->getSession($menu->id, $node->siteId, $userId)->stagedDeletes)->toHaveCount(1);
    $sessions->discard($owner);
    $node = Node::find()->id($node->id)->status(null)->one();
    $node->setPendingPublish(true); $node->enabled = false; $node->setEnabledForSite(false);
    Craft::$app->elements->saveElement($node);
    $owner = $sessions->getOrCreate($menu->id, $node->siteId, $userId);
    $sessions->stageAddedNodes($owner, [$node->id]);
    $variant = Node::find()->id($node->id)->siteId($secondary->id)->status(null)->one();
    expect(fn() => $sessions->stageDelete($other, $variant))->toThrow(yii\base\UserException::class);
    expect(Node::find()->id($node->id)->status(null)->one())->not->toBeNull();
    $sessions->discard($owner);
});
