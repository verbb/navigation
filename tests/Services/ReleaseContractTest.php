<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;
use verbb\navigation\elements\Menu;

it('release contract denies builder duplication of another session addition', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Private', '/private');
    $node->setPendingPublish(true);
    $node->enabled = false;
    Craft::$app->elements->saveElement($node);
    $owner = new craft\elements\User(['username' => uniqid('owner'), 'email' => uniqid('owner') . '@example.test']);
    Craft::$app->elements->saveElement($owner);
    $sessions = N::$plugin->getBuildSessions();
    $session = $sessions->getOrCreate($menu->id, $node->siteId, $owner->id);
    $sessions->stageAddedNodes($session, [$node->id]);
    boundaryRequest(function() use ($menu, $node) {
        Craft::$app->request->setBodyParams(['menuId' => $menu->id, 'siteId' => $node->siteId, 'nodeIds' => [$node->id]]);
        expect(fn() => (new verbb\navigation\controllers\BuilderController('builder', N::$plugin))->actionDuplicateNodes())
            ->toThrow(yii\web\ForbiddenHttpException::class);
        expect((int)Node::find()->menuId($menu->id)->status(null)->count())->toBe(1);
    });
});

it('release contract duplicates menu hierarchy into its destination structure', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    F::customNode($menu, 'Child', '/child', $parent);
    boundaryRequest(function() use ($menu) {
        Craft::$app->request->setBodyParams(['id' => $menu->id]);
        $before = array_column(N::$plugin->getMenus()->getAllMenus(), 'id');
        $response = (new verbb\navigation\controllers\MenusController('menus', N::$plugin))->actionDuplicateMenu();
        expect($response->statusCode)->toBe(200);
        $copies = array_values(array_filter(N::$plugin->getMenus()->getAllMenus(), fn($m) => !in_array($m->id, $before)));
        expect($copies)->toHaveCount(1);
        $copy = $copies[0];
        $nodes = Node::find()->menuId($copy->id)->status(null)->all();
        expect($nodes)->toHaveCount(2);
        expect($nodes[1]->getParent()?->id)->toBe($nodes[0]->id);
        expect($nodes[0]->structureId)->toBe($copy->structureId);
        expect((int)Node::find()->menuId($menu->id)->count())->toBe(2);
    });
});

it('release contract rolls back menu removal when a node vetoes deletion', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    $child = F::customNode($menu, 'Child', '/child', $parent);
    $veto = static function($event) use ($parent) { if ($event->sender->id === $parent->id) $event->isValid = false; };
    yii\base\Event::on(Node::class, Node::EVENT_BEFORE_DELETE, $veto);
    $error = null;
    try { N::$plugin->getMenus()->deleteMenu($menu); } catch (Throwable $e) { $error = $e; }
    finally { yii\base\Event::off(Node::class, Node::EVENT_BEFORE_DELETE, $veto); }
    expect($error)->not->toBeNull();
    N::$plugin->getMenus()->resetCache();
    expect(N::$plugin->getMenus()->getMenuById($menu->id))->not->toBeNull();
    expect((int)Node::find()->menuId($menu->id)->count())->toBe(2);
    expect(Node::find()->id($child->id)->one()->getParentId())->toBe($parent->id);
    expect(Craft::$app->projectConfig->get('navigation.menus.' . $menu->uid))->not->toBeNull();
});

it('release contract persists the submitted draft revision without adopting a newer tree', function() {
    $menu = F::menu();
    $a = F::customNode($menu, 'A', '/a');
    $b = F::customNode($menu, 'B', '/b');
    $original = verbb\navigation\helpers\BuilderStructureRevision::get($menu);
    $moves = [['elementId' => $a->id, 'parentId' => null, 'prevId' => null],
        ['elementId' => $b->id, 'parentId' => null, 'prevId' => $a->id]];
    Craft::$app->structures->prependToRoot($menu->structureId, $b);
    boundaryRequest(function() use ($menu, $a, $moves, $original) {
        Craft::$app->request->setBodyParams(['menuId' => $menu->id, 'siteId' => $a->siteId,
            'structureMoves' => $moves, 'structureRevision' => $original]);
        $controller = new verbb\navigation\controllers\BuilderController('builder', N::$plugin);
        expect($controller->actionSaveDraft()->statusCode)->toBe(200);
        $state = $controller->actionGetState()->data;
        expect($state['session']['structureRevision'])->toBe($original);
        expect($state['structureRevision'])->not->toBe($original);
        Craft::$app->request->setBodyParams(['menuId' => $menu->id, 'siteId' => $a->siteId,
            'applyStructure' => true, 'moves' => $state['session']['structureMoves'],
            'structureRevision' => $state['session']['structureRevision']]);
        expect(fn() => (new verbb\navigation\controllers\BuildSessionsController('build-sessions', N::$plugin))->actionPublish())
            ->toThrow(yii\web\ConflictHttpException::class);
    });
});

it('release contract keeps canonical menu deletion on its configuration route', function() {
    $menu = F::menu();
    boundaryRequest(function() use ($menu) {
        $element = Menu::find()->id($menu->id)->one();
        expect($element->canSave(Craft::$app->user->identity))->toBeTrue();
        expect(Craft::$app->elements->canDelete($element))->toBeFalse();
    });
});

it('release contract restores configuration after a late menu duplication failure', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    F::customNode($menu, 'Child', '/child', $parent);
    $beforeConfig = Craft::$app->projectConfig->get('navigation');
    $beforeMenus = count(N::$plugin->getMenus()->getAllMenus());
    $beforeNodes = (int)Node::find()->status(null)->count();
    $veto = static function($event) {
        if ($event->sender->duplicateOf && $event->sender->title === 'Child') $event->isValid = false;
    };
    yii\base\Event::on(Node::class, Node::EVENT_BEFORE_SAVE, $veto);
    try {
        boundaryRequest(function() use ($menu) {
            Craft::$app->request->setBodyParams(['id' => $menu->id]);
            expect(fn() => (new verbb\navigation\controllers\MenusController('menus', N::$plugin))->actionDuplicateMenu())
                ->toThrow(craft\errors\InvalidElementException::class);
        });
    } finally { yii\base\Event::off(Node::class, Node::EVENT_BEFORE_SAVE, $veto); }
    expect(Craft::$app->projectConfig->get('navigation'))->toEqual($beforeConfig);
    expect(count(N::$plugin->getMenus()->getAllMenus()))->toBe($beforeMenus);
    expect((int)Node::find()->status(null)->count())->toBe($beforeNodes);
});

it('release contract reads an unplaced node without recursive projection queries', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Unplaced', '/unplaced');
    Craft::$app->structures->remove($menu->structureId, $node);
    Tests\Support\WebRequestSimulator::withAbsoluteUrl('https://boundary.invalid/unplaced', function() use ($node) {
        $loaded = Node::find()->id($node->id)->one();
        expect($loaded->id)->toBe($node->id);
        expect($loaded->lft)->toBeNull();
    });
});

it('release contract rejects a move whose actual subtree exceeds menu depth', function() {
    $menu = F::menu();
    $menu->maxLevels = 2;
    N::$plugin->getMenus()->saveMenu($menu);
    $menu = N::$plugin->getMenus()->getMenuById($menu->id);
    $a = F::customNode($menu, 'A', '/a');
    $b = F::customNode($menu, 'B', '/b', $a);
    $x = F::customNode($menu, 'X', '/x');
    $y = F::customNode($menu, 'Y', '/y', $x);
    $revision = verbb\navigation\helpers\BuilderStructureRevision::get($menu);
    expect(fn() => verbb\navigation\helpers\BuilderStructureRevision::apply($menu, $revision,
        fn() => N::$plugin->getBuildSessions()->applyStructureMoves($menu, $a->siteId,
            [['elementId' => $x->id, 'parentId' => null, 'prevId' => $b->id]])))
        ->toThrow(yii\web\BadRequestHttpException::class);
    expect(Node::find()->id($y->id)->one()->level)->toBe(2);
    expect(verbb\navigation\helpers\BuilderStructureRevision::get($menu))->toBe($revision);
});

it('release contract authorizes private descendants before copying a subtree', function(string $action) {
    $target = F::secondarySite();
    $menu = F::menu(null, verbb\navigation\models\MenuSettings::PROPAGATION_METHOD_NONE);
    $parent = F::customNode($menu, 'Public parent', '/parent');
    $child = F::customNode($menu, 'Private child', '/child', $parent);
    $child->setPendingPublish(true);
    $child->enabled = false;
    Craft::$app->elements->saveElement($child);
    $owner = new craft\elements\User(['username' => uniqid('owner'), 'email' => uniqid('owner') . '@example.test']);
    Craft::$app->elements->saveElement($owner);
    $sessions = N::$plugin->getBuildSessions();
    $session = $sessions->getOrCreate($menu->id, $child->siteId, $owner->id);
    $sessions->stageAddedNodes($session, [$child->id]);
    boundaryRequest(function() use ($action, $menu, $parent, $target) {
        Craft::$app->request->setBodyParams(['menuId' => $menu->id, 'sourceSiteId' => $parent->siteId,
            'siteId' => $action === 'copy' ? $target->id : $parent->siteId, 'nodeIds' => [$parent->id], 'deep' => true]);
        $call = $action === 'copy'
            ? fn() => (new verbb\navigation\controllers\NodesController('nodes', N::$plugin))->actionCopyToSite()
            : fn() => (new verbb\navigation\controllers\BuilderController('builder', N::$plugin))->actionDuplicateNodes();
        expect($call)->toThrow(yii\web\ForbiddenHttpException::class);
        expect((int)Node::find()->menuId($menu->id)->site('*')->unique()->status(null)->count())->toBe(2);
    });
})->with(['copy', 'duplicate']);

it('release contract does not create unowned pending copies on another site or menu', function() {
    $target = F::secondarySite();
    $menu = F::menu(null, verbb\navigation\models\MenuSettings::PROPAGATION_METHOD_NONE);
    $node = F::customNode($menu, 'Own pending', '/own');
    $node->setPendingPublish(true);
    $node->enabled = false;
    Craft::$app->elements->saveElement($node);
    boundaryRequest(function() use ($menu, $node, $target) {
        $sessions = N::$plugin->getBuildSessions();
        $sessions->stageAddedNodes($sessions->getOrCreate($menu->id, $node->siteId), [$node->id]);
        Craft::$app->request->setBodyParams(['menuId' => $menu->id, 'sourceSiteId' => $node->siteId,
            'siteId' => $target->id, 'nodeIds' => [$node->id]]);
        expect(fn() => (new verbb\navigation\controllers\NodesController('nodes', N::$plugin))->actionCopyToSite())
            ->toThrow(yii\web\BadRequestHttpException::class);
        Craft::$app->request->setBodyParams(['id' => $menu->id]);
        expect(fn() => (new verbb\navigation\controllers\MenusController('menus', N::$plugin))->actionDuplicateMenu())
            ->toThrow(yii\web\BadRequestHttpException::class);
        expect((int)Node::find()->menuId($menu->id)->site('*')->unique()->status(null)->count())->toBe(1);
    });
});

it('release contract upgrades legacy sessions without assigning a guessed draft revision', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Draft', '/draft');
    boundaryRequest(function() use ($menu, $node) {
        $sessions = N::$plugin->getBuildSessions();
        $session = $sessions->getOrCreate($menu->id, $node->siteId);
        $moves = [['elementId' => $node->id, 'parentId' => null, 'prevId' => null]];
        $sessions->saveDraft($session, $moves);
        $db = Craft::$app->db;
        $migration = new verbb\navigation\migrations\m260912_000000_build_session_revision();
        $db->createCommand()->dropColumn('{{%navigation_build_sessions}}', 'structureRevision')->execute();
        $db->schema->refreshTableSchema('{{%navigation_build_sessions}}');
        try { expect($migration->safeUp())->toBeTrue(); }
        finally {
            if (!$db->columnExists('{{%navigation_build_sessions}}', 'structureRevision')) $migration->safeUp();
            $db->schema->refreshTableSchema('{{%navigation_build_sessions}}');
        }
        expect($migration->safeUp())->toBeTrue();
        $restored = $sessions->getSession($menu->id, $node->siteId);
        expect($restored->structureMoves)->toBe($moves);
        expect($restored->structureRevision)->toBeNull();
    });
});

it('release contract gives menu copies independent layouts and preserves custom content', function() {
    $menu = F::menu();
    $field = F::addPlainTextFieldToMenu($menu);
    $menu = N::$plugin->getMenus()->getMenuById($menu->id);
    $layout = new craft\models\FieldLayout(['type' => Menu::class]);
    $layout->setTabs([new craft\models\FieldLayoutTab(['layout' => $layout, 'name' => 'Content',
        'elements' => [['type' => craft\fieldlayoutelements\CustomField::class, 'fieldUid' => $field->uid]]])]);
    $menu->setMenuFieldLayout($layout);
    N::$plugin->getMenus()->saveMenu($menu);
    $menu = N::$plugin->getMenus()->getMenuById($menu->id);
    $node = F::customNodeWithField($menu, $field, 'node content');
    N::$plugin->getMenus()->saveMenuContentFromDraft($menu->id, $node->siteId, [$field->handle => 'menu content']);
    boundaryRequest(function() use ($menu, $node, $field) {
        $ids = array_column(N::$plugin->getMenus()->getAllMenus(), 'id');
        Craft::$app->request->setBodyParams(['id' => $menu->id]);
        (new verbb\navigation\controllers\MenusController('menus', N::$plugin))->actionDuplicateMenu();
        $copies = array_values(array_filter(N::$plugin->getMenus()->getAllMenus(), fn($m) => !in_array($m->id, $ids)));
        expect($copies)->toHaveCount(1);
        $copy = $copies[0];
        expect($copy->fieldLayoutId)->not->toBe($menu->fieldLayoutId);
        expect($copy->menuFieldLayoutId)->not->toBe($menu->menuFieldLayoutId)->not->toBeNull();
        expect(Node::find()->menuId($copy->id)->one()->getFieldValue($field->handle))->toBe('node content');
        expect(Menu::find()->id($copy->id)->one()->getFieldValue($field->handle))->toBe('menu content');
        N::$plugin->getMenus()->deleteMenu($copy);
        expect(Node::find()->id($node->id)->one()->getFieldValue($field->handle))->toBe('node content');
        expect(Menu::find()->id($menu->id)->one()->getFieldValue($field->handle))->toBe('menu content');
    });
});
