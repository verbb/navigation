<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\elements\Node;
use verbb\navigation\fieldlayoutelements\CustomAttributesField;
use verbb\navigation\fieldlayoutelements\NewWindowField;
use verbb\navigation\fieldlayoutelements\NodeTypeElements;
use verbb\navigation\Navigation;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Dynamic;

it('disables the linked-element picker in a read-only node editor', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::elementNode($menu, $entry);
    $html = W::withAbsoluteUrl('https://navigation.test/admin/navigation', fn() => (new NodeTypeElements())->formHtml($node, true));

    expect($html)->toContain('disabled')
        ->and($html)->not->toContain('new Craft.Navigation.ElementSelect');
});

it('disables the node type selector without registering its change handler', function() {
    $menu = F::menu();
    foreach (Navigation::$plugin->getNodeTypes()->getRegisteredNodeTypes() as $nodeType) {
        $menu->permissions[$nodeType::class] = ['enabled' => $nodeType instanceof Custom];
    }
    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    $node = F::customNode($menu, 'Read-only node', '/read-only');

    [$html, $registeredJs] = W::withAbsoluteUrl('https://navigation.test/admin/navigation', function() use ($node) {
        $method = new ReflectionMethod($node, 'metaFieldsHtml');
        $method->setAccessible(true);
        $html = $method->invoke($node, true);

        return [$html, json_encode(Craft::$app->getView()->js)];
    });

    expect($html)->toMatch('/<select(?=[^>]*name="type")(?=[^>]*disabled)[^>]*>/')
        ->and($registeredJs)->not->toContain('const getEditor');
});

it('disables custom node-type controls in a read-only node editor', function() {
    $menu = F::menu();
    $node = new Node([
        'menuId' => $menu->id,
        'siteId' => Craft::$app->getSites()->getPrimarySite()->id,
        'type' => Dynamic::class,
        'data' => ['dynamicSource' => 'entrySection', 'sectionId' => ''],
    ]);

    $html = W::withAbsoluteUrl(
        'https://navigation.test/admin/navigation',
        fn() => (new NodeTypeElements())->formHtml($node, true),
    );

    expect($html)->toMatch('/<fieldset(?=[^>]*class="navigation-node-type-fields")(?=[^>]*disabled)[^>]*>/');
});

it('disables native Navigation fields in a read-only node editor', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Read-only native fields', '/read-only-native');
    $node->customAttributes = [['attribute' => 'data-test', 'value' => 'value']];

    [$newWindowHtml, $attributesHtml] = W::withAbsoluteUrl(
        'https://navigation.test/admin/navigation',
        fn() => [
            (new NewWindowField())->inputHtml($node, true),
            (new CustomAttributesField())->inputHtml($node, true),
        ],
    );

    expect($newWindowHtml)->toContain('disabled')
        ->and($attributesHtml)->not->toContain('Add a row')
        ->and($attributesHtml)->toContain('class="editable fullwidth static"')
        ->and($attributesHtml)->not->toContain('<input');
});
