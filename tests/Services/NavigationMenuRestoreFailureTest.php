<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Menu;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation;
use yii\base\Event;
use yii\base\UserException;

it('keeps a cancelled menu restoration atomic and retryable', function(string $target) {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    $child = F::customNode($menu, 'Child', '/parent/child', $parent);
    $menus = Navigation::$plugin->getMenus();
    expect($menus->deleteMenu($menu))->toBeTrue();
    $configPath = 'navigation.menus.' . $menu->uid;
    $configBefore = Craft::$app->projectConfig->get($configPath);
    $class = match ($target) {
        'menu' => Menu::class,
        'placement', 'reposition' => craft\services\Structures::class,
        default => Node::class,
    };
    $eventName = match ($target) {
        'placement' => craft\services\Structures::EVENT_BEFORE_INSERT_ELEMENT,
        'reposition' => craft\services\Structures::EVENT_BEFORE_UPDATE_ELEMENT,
        default => $class::EVENT_BEFORE_RESTORE,
    };
    $id = $target === 'menu' ? $menu->id : $parent->id;
    $veto = static function($event) use ($id, $target) {
        $element = in_array($target, ['placement', 'reposition'], true) ? $event->element : $event->sender;
        if ($element->id === $id) {
            $event->isValid = false;
        }
    };
    Event::on($class, $eventName, $veto);
    try {
        expect(fn() => $menus->saveMenu($menu))->toThrow(UserException::class);
    } finally {
        Event::off($class, $eventName, $veto);
    }

    expect($menus->getMenuById($menu->id))->toBeNull();
    expect(Menu::find()->id($menu->id)->status(null)->trashed()->exists())->toBeTrue();
    expect((int)Node::find()->id([$parent->id, $child->id])->status(null)->count())->toBe(0);
    expect((int)Node::find()->id([$parent->id, $child->id])->status(null)->trashed()->count())->toBe(2);
    expect(Craft::$app->projectConfig->get($configPath))->toBe($configBefore);

    expect($menus->saveMenu($menu))->toBeTrue();
    expect($menus->getMenuById($menu->id))->not->toBeNull();
    expect(Menu::find()->id($menu->id)->status(null)->exists())->toBeTrue();
    $restoredParent = Node::find()->id($parent->id)->status(null)->one();
    $restoredChild = Node::find()->id($child->id)->status(null)->one();
    expect($restoredParent)->not->toBeNull();
    expect($restoredChild)->not->toBeNull();
    expect($restoredChild->getParentId())->toBe($parent->id);
    expect($restoredParent->deletedWithMenu)->toBeFalse();
    expect($restoredChild->deletedWithMenu)->toBeFalse();
})->with(['parent', 'menu', 'placement', 'reposition']);
