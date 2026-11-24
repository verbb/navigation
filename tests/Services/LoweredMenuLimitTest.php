<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\nodetypes\Custom;

// Menu settings accept a limit below the menu's current size. The menu must stay editable so it can be brought
// back under the limit, while anything that adds to the breach is still refused.

function loweredLimitMenu(MenuSettings $menu, array $limits): MenuSettings
{
    foreach ($limits as $key => $value) {
        $menu->$key = $value;
    }

    expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();

    return N::$plugin->getMenus()->getMenuById($menu->id);
}

// A new node is refused by validation or, when validation passes, by the structure check.
function loweredLimitSavesExtraNode(MenuSettings $menu): bool
{
    $node = new Node(['menuId' => $menu->id, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id, 'type' => Custom::class, 'title' => 'Extra', 'url' => '/extra']);

    try {
        return Craft::$app->elements->saveElement($node);
    } catch (yii\base\UserException) {
        return false;
    }
}

it('keeps nodes editable and deletable once Max Nodes is below the node count', function() {
    $menu = F::menu();
    [$a, $b, $c] = F::flatCustomNodes($menu, 3);
    $menu = loweredLimitMenu($menu, ['maxNodes' => 1]);

    $a->title = 'Edited';
    $a->enabled = false;
    expect(Craft::$app->elements->saveElement($a))->toBeTrue();

    $sessions = N::$plugin->getBuildSessions();
    $session = $sessions->getOrCreate($menu->id, $b->siteId);
    $sessions->stageDelete($session, $b);
    expect($sessions->publish($session)['deletedCount'])->toBe(1);

    expect(loweredLimitSavesExtraNode($menu))->toBeFalse();

    expect(Craft::$app->elements->deleteElement($c, true))->toBeTrue();
    expect(Node::find()->menuId($menu->id)->status(null)->ids())->toEqual([$a->id]);
    expect(Node::find()->id($a->id)->status(null)->one()->title)->toBe('Edited');
});

it('lets editors trash, restore and permanently delete entries linked from a menu over Max Nodes', function() {
    $menu = F::menu();
    [$first, $second] = F::entries(2);
    $node = F::entryNode($menu, $first);
    F::entryNode($menu, $second);
    loweredLimitMenu($menu, ['maxNodes' => 1]);

    expect(Craft::$app->elements->deleteElement($first))->toBeTrue();
    expect(Node::find()->id($node->id)->status(null)->one()->getIsDisabledByLinkedElement())->toBeTrue();

    expect(Craft::$app->elements->restoreElement($first))->toBeTrue();
    expect(Node::find()->id($node->id)->status(null)->one()->getIsDisabledByLinkedElement())->toBeFalse();

    expect(Craft::$app->elements->deleteElement($first, true))->toBeTrue();
    expect(Node::find()->id($node->id)->status(null)->one())->toBeNull();
});

it('allows removing over-deep nodes one at a time but not moving another node too deep', function() {
    $menu = F::menu();
    $a = F::customNode($menu, 'A', '/a');
    $b = F::customNode($menu, 'B', '/b', $a);
    $c = F::customNode($menu, 'C', '/c', $b);
    F::customNode($menu, 'D', '/d', $b);
    $x = F::customNode($menu, 'X', '/x');
    $menu = loweredLimitMenu($menu, ['maxLevels' => 2]);

    $sessions = N::$plugin->getBuildSessions();
    $session = $sessions->getOrCreate($menu->id, $a->siteId);
    $sessions->stageDelete($session, $c);
    expect($sessions->publish($session)['deletedCount'])->toBe(1);

    expect(Craft::$app->structures->append($menu->structureId, $x, $b))->toBeFalse();
    expect(Node::find()->id($x->id)->one()->level)->toBe(1);
});

it('allows deleting and reordering nodes on a level over its limit but not adding to it', function() {
    $menu = F::menu();
    [$a, $b, $c] = F::flatCustomNodes($menu, 3);
    $menu = loweredLimitMenu($menu, ['maxNodesSettings' => [['level' => 1, 'max' => 1]]]);

    N::$plugin->getBuildSessions()->applyStructureMoves($menu, $a->siteId, [
        ['elementId' => $c->id, 'parentId' => null, 'prevId' => null],
    ]);
    expect(Node::find()->menuId($menu->id)->status(null)->ids())->toEqual([$c->id, $a->id, $b->id]);

    expect(Craft::$app->elements->deleteElement($a, true))->toBeTrue();
    expect(loweredLimitSavesExtraNode($menu))->toBeFalse();
    expect(Node::find()->menuId($menu->id)->status(null)->ids())->toEqual([$c->id, $b->id]);
});

it('refuses to publish staged nodes that a lowered Max Nodes has no room for', function() {
    $menu = F::menu();
    F::flatCustomNodes($menu, 2);
    $pending = F::customNode($menu, 'Pending', '/pending');
    $pending->enabled = false;
    $pending->setEnabledForSite(false);
    $pending->setPendingPublish(true);
    expect(Craft::$app->elements->saveElement($pending))->toBeTrue();

    $sessions = N::$plugin->getBuildSessions();
    $session = $sessions->getOrCreate($menu->id, $pending->siteId);
    $sessions->stageAddedNodes($session, [$pending->id]);
    loweredLimitMenu($menu, ['maxNodes' => 2]);

    expect(fn() => $sessions->publish($session))->toThrow(yii\web\BadRequestHttpException::class);
    expect(Node::find()->id($pending->id)->status(null)->one()->getIsPendingPublish())->toBeTrue();
});

it('refuses to move an existing node into a level already over its limit', function() {
    $menu = F::menu();
    $a = F::customNode($menu, 'A', '/a');
    F::customNode($menu, 'B', '/b', $a);
    F::customNode($menu, 'C', '/c', $a);
    $x = F::customNode($menu, 'X', '/x');
    loweredLimitMenu($menu, ['maxNodesSettings' => [['level' => 2, 'max' => 1]]]);

    $x->setParentId($a->id);
    expect(fn() => Craft::$app->elements->saveElement($x))->toThrow(yii\base\UserException::class);
    expect(Node::find()->id($x->id)->one()->getParentId())->toBeNull();
});

it('refuses a delete whose promoted children grow a level already over its limit', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    F::customNode($menu, 'B', '/b', $parent);
    F::customNode($menu, 'C', '/c', $parent);
    F::flatCustomNodes($menu, 2);
    loweredLimitMenu($menu, ['maxNodesSettings' => [['level' => 1, 'max' => 1]]]);

    expect(fn() => Craft::$app->elements->deleteElement($parent, true))->toThrow(yii\web\BadRequestHttpException::class);
    expect(Node::find()->id($parent->id)->one())->not->toBeNull();
});
