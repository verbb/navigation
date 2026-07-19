<?php

declare(strict_types=1);

use craft\db\Query;
use craft\db\Table;
use craft\elements\Entry;
use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\elements\Menu;
use verbb\navigation\Navigation;

it('creates menu elements that do not reclaim existing entry ids', function() {
    $entry = NavigationFixtureFactory::entries(1)[0];
    $entryId = (int)$entry->id;
    $entryTitle = $entry->title;

    $menu = NavigationFixtureFactory::menu();

    expect($menu->id)->not->toBe($entryId);

    $menuElement = Menu::find()->id($menu->id)->status(null)->one();
    expect($menuElement)->not->toBeNull();
    expect($menuElement->id)->toBe($menu->id);

    expect(
        (new Query())->select(['type'])->from([Table::ELEMENTS])->where(['id' => $entryId])->scalar()
    )->toBe(Entry::class);

    expect(Entry::find()->id($entryId)->status(null)->one()?->title)->toBe($entryTitle);

    // Saving the menu again must not retype the entry or wipe its title.
    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();

    expect(
        (new Query())->select(['type'])->from([Table::ELEMENTS])->where(['id' => $entryId])->scalar()
    )->toBe(Entry::class);

    expect(Entry::find()->id($entryId)->status(null)->one()?->title)->toBe($entryTitle);
});
