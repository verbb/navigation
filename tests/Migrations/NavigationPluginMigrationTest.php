<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\Fixtures\PluginMigrationFixture as Source;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation;
use verbb\navigation\variables\NavigationVariable;

it('detects installed source tables and includes ready sources in settings', function(string $provider) {
    Source::with($provider, function($fixture) use ($provider) {
        $migrations = Navigation::$plugin->getMigrations();
        expect($migrations->getSources()[$provider]['ready'])->toBeTrue();
        expect(array_column($migrations->getSourceMenus($provider),'handle'))->toBe([$fixture['handle']]);
        expect((new NavigationVariable())->getSettingsNavItems())->toBeArray()->toHaveKey('migrate/' . $provider);
    });
})->with(['navkit','free-nav','tka-navigation']);

it('migrates provider hierarchy links and output without changing its source', function(string $provider, bool $legacy) {
    Source::with($provider, function($f) use ($provider) {
        $class = Navigation::$plugin->getMigrations()->getMigratorClass($provider);
        $result = Navigation::$plugin->createMigrator($class, ['handle'=>$f['handle']])->run();
        expect($result->ok)->toBeTrue(json_encode($result->lines));
        $menu = Navigation::$plugin->getMenus()->getMenuByHandle($f['handle']);
        expect($menu)->not->toBeNull();
        $nodes = Node::find()->menuId($menu->id)->siteId($f['primary']->id)->status(null)->all();
        expect($nodes)->toHaveCount($provider === 'tka-navigation' ? 2 : 3);
        expect($nodes[1]->getParent()?->id)->toBe($nodes[0]->id);
        expect($nodes[1]->elementId)->toBe($f['linked']->id);
        expect($nodes[1]->newWindow)->toBeTrue();
        if ($provider === 'tka-navigation') {
            expect($nodes[0]->getUrl())->toBe('/provider-root');
            expect($nodes[1]->urlSuffix)->toBe('#details');
            expect($result->stats['warnings'] ?? 0)->toBeGreaterThan(0);
        } else {
            expect($nodes[0]->getUrl())->toBe($provider === 'free-nav' ? '/provider-0?source=migration' : '/provider-0');
            expect($nodes[0]->classes)->toBe('provider-class');
            expect($nodes[2]->getEnabledForSite())->toBeFalse();
            expect(Node::find()->id($nodes[2]->id)->siteId($f['secondary']->id)->one())->toBeNull();
            expect(Node::find()->menuId($menu->id)->siteId($f['primary']->id)->all())->toHaveCount(2);
        }
        expect(array_column($class::getMenus(),'handle'))->toBe([$f['handle']]);
    }, $legacy);
})->with(['Navkit'=>['navkit',false], 'FreeNav current'=>['free-nav',false], 'FreeNav historical'=>['free-nav',true], 'TKA'=>['tka-navigation',false]]);

it('skips an existing destination handle without replacing it', function() {
    Source::with('free-nav', function($f) {
        $existing = F::menu($f['handle']);
        F::customNode($existing, 'Keep this node', '/keep');
        $class = Navigation::$plugin->getMigrations()->getMigratorClass('free-nav');
        $result = Navigation::$plugin->createMigrator($class,['handle'=>$f['handle'],'skipExisting'=>true])->run();
        expect($result->ok)->toBeTrue();
        expect($result->stats['menusSkipped'] ?? 0)->toBe(1);
        expect(Node::find()->menuId($existing->id)->siteId($f['primary']->id)->one()?->title)->toBe('Keep this node');
    });
});
