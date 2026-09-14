<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;

it('sanitizes menu instructions at the builder output boundary while retaining Markdown', function() {
    $menu = F::menu();
    $menu->instructions = '**Help** <img src="invalid" onerror="alert(1)"> [unsafe](javascript:alert(1)) [guide](https://example.test/guide)';
    N::$plugin->getMenus()->saveMenu($menu);
    boundaryRequest(function() use ($menu) {
        $state = N::$plugin->getBuilderState()->getState($menu->id, Craft::$app->sites->getPrimarySite()->id);
        $html = $state['menu']['instructionsHtml'];
        expect($html)->toContain('<strong>Help</strong>');
        expect($html)->toContain('href="https://example.test/guide"');
        expect($html)->not->toContain('onerror');
        expect($html)->not->toContain('href="javascript:');
        expect($state['menu']['instructions'])->toBe($menu->instructions);
    });
});
