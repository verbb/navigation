<?php

declare(strict_types=1);

use craft\models\GqlSchema;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\fields\NavigationField;

it('enforces menu and projected element grants through a Navigation field response', function() {
    $menu = F::menu();
    $deniedMenu = F::menu();
    F::customNode($deniedMenu, 'Private menu link', '/private');
    $allowed = F::entrySection();
    $denied = F::entrySection();
    $entry = F::entries(1, $allowed)[0];
    $deniedEntry = F::entries(1, $denied)[0];
    $allowedNode = F::dynamicSectionNode($menu, $allowed);
    $deniedNode = F::dynamicSectionNode($menu, $denied);

    $holderSection = F::entrySection();
    $type = $holderSection->getEntryTypes()[0];
    $field = new NavigationField(['name' => 'Selected menu', 'handle' => 'selectedMenu' . uniqid()]);
    expect(Craft::$app->getFields()->saveField($field))->toBeTrue();
    $layout = $type->getFieldLayout();
    $tab = $layout->getTabs()[0];
    $tab->setElements(array_merge($tab->getElements(), [Craft::$app->getFields()->createLayoutElement([
        'type' => \craft\fieldlayoutelements\CustomField::class,
        'fieldUid' => $field->uid,
    ])]));
    expect(Craft::$app->getEntries()->saveEntryType($type))->toBeTrue();
    [$holder, $deniedHolder, $emptyHolder] = F::entries(3, $holderSection);
    foreach ([[$holder, $menu->handle], [$deniedHolder, $deniedMenu->handle]] as [$item, $handle]) {
        $item->setFieldValue($field->handle, $handle);
        expect(Craft::$app->getElements()->saveElement($item))->toBeTrue();
    }

    $schema = new GqlSchema(['name' => 'Field projection scope', 'scope' => [
        'navigationMenus.' . $menu->uid . ':read',
        'sections.' . $allowed->uid . ':read',
        'sections.' . $holderSection->uid . ':read',
    ]]);
    $query = 'query($ids: [QueryArgument]) { entries(id: $ids, orderBy: "id asc") { id ... on ' . $type->handle . '_Entry { selected: ' . $field->handle . '(level: 1, withNodeHierarchy: true) { id children { id element { id } } } } } }';
    $result = Craft::$app->getGql()->executeQuery($schema, $query, ['ids' => [$holder->id, $deniedHolder->id, $emptyHolder->id]]);
    expect($result)->not->toHaveKey('errors');
    expect($result['data']['entries'])->toBe([
        ['id' => (string)$holder->id, 'selected' => [
            ['id' => (string)$allowedNode->id, 'children' => [['id' => 'projected:' . $entry->id, 'element' => ['id' => (string)$entry->id]]]],
            ['id' => (string)$deniedNode->id, 'children' => [['id' => 'projected:' . $deniedEntry->id, 'element' => null]]],
        ]],
        ['id' => (string)$deniedHolder->id, 'selected' => []],
        ['id' => (string)$emptyHolder->id, 'selected' => []],
    ]);
});
