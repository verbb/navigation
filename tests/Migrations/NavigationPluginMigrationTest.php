<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\Fixtures\PluginMigrationFixture as Source;
use verbb\navigation\elements\Node;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Dynamic;
use verbb\navigation\nodetypes\Passive;
use verbb\navigation\Navigation;
use verbb\navigation\variables\NavigationVariable;

it('detects installed source tables and includes ready sources in settings', function(string $provider) {
    Source::with($provider, function($fixture) use ($provider) {
        $migrations = Navigation::$plugin->getMigrations();
        expect($migrations->getSources()[$provider]['ready'])->toBeTrue();
        expect(array_column($migrations->getSourceMenus($provider),'handle'))->toBe([$fixture['handle']]);
        expect((new NavigationVariable())->getSettingsNavItems())->toBeArray()->toHaveKey('migrate/' . $provider);
    });
})->with(['navkit','free-nav','menu-builder','tka-navigation']);

it('migrates provider hierarchy links and output without changing its source', function(string $provider, bool $legacy) {
    Source::with($provider, function($f) use ($provider) {
        $class = Navigation::$plugin->getMigrations()->getMigratorClass($provider);
        $result = Navigation::$plugin->createMigrator($class, ['handle'=>$f['handle']])->run();
        expect($result->ok)->toBeTrue(json_encode($result->lines));
        $menu = Navigation::$plugin->getMenus()->getMenuByHandle($f['handle']);
        expect($menu)->not->toBeNull();
        $nodes = Node::find()->menuId($menu->id)->siteId($f['primary']->id)->status(null)->all();
        expect($nodes)->toHaveCount(match ($provider) {
            'tka-navigation' => 2,
            'menu-builder' => 9,
            default => 3,
        });
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
            $disabledNode = $provider === 'menu-builder'
                ? array_values(array_filter($nodes, static fn(Node $node): bool => $node->title === 'Provider node 3'))[0]
                : $nodes[2];
            if ($provider === 'menu-builder') {
                expect($disabledNode->enabled)->toBeFalse();
            } else {
                expect($disabledNode->getEnabledForSite())->toBeFalse();
            }
            expect(Node::find()->id($disabledNode->id)->siteId($f['secondary']->id)->one())->toBeNull();
            expect(Node::find()->menuId($menu->id)->siteId($f['primary']->id)->all())->toHaveCount($provider === 'menu-builder' ? 7 : 2);
        }
        expect(array_column($class::getMenus(),'handle'))->toBe([$f['handle']]);
    }, $legacy);
})->with(['Navkit'=>['navkit',false], 'FreeNav current'=>['free-nav',false], 'FreeNav historical'=>['free-nav',true], 'MenuBuilder'=>['menu-builder',false], 'TKA'=>['tka-navigation',false]]);

it('maps MenuBuilder menu restrictions attributes dynamic sources and retained metadata', function() {
    Source::with('menu-builder', function($f) {
        $class = Navigation::$plugin->getMigrations()->getMigratorClass('menu-builder');
        $result = Navigation::$plugin->createMigrator($class, ['handle' => $f['handle']])->run();
        $menu = Navigation::$plugin->getMenus()->getMenuByHandle($f['handle']);

        expect($result->ok)->toBeTrue(json_encode($result->lines))
            ->and($result->stats['warnings'] ?? 0)->toBeGreaterThanOrEqual(3)
            ->and($menu)->not->toBeNull()
            ->and($menu->instructions)->toBe('MenuBuilder migration fixture')
            ->and($menu->maxLevels)->toBe(3);

        $siteSettings = $menu->getSiteSettings();
        expect($siteSettings[$f['primary']->id]->enabled)->toBeTrue()
            ->and($siteSettings[$f['secondary']->id]->enabled)->toBeFalse();

        $nodes = Node::find()->menuId($menu->id)->siteId($f['primary']->id)->status(null)->all();
        $root = array_values(array_filter($nodes, static fn(Node $node): bool => $node->title === 'Provider node 1'))[0];
        $dynamic = array_values(array_filter($nodes, static fn(Node $node): bool => $node->type === Dynamic::class))[0];
        $byTitle = [];

        foreach ($nodes as $node) {
            $byTitle[$node->title] = $node;
        }

        expect($root->customAttributes)->toContain(
            ['attribute' => 'data-source', 'value' => 'migration'],
            ['attribute' => 'rel', 'value' => 'nofollow'],
            ['attribute' => 'id', 'value' => 'provider-root'],
            ['attribute' => 'aria-label', 'value' => 'Provider root'],
        )->and($root->data['migratedMenuBuilder']['visibility'])->toBe([['type' => 'loggedIn']])
            ->and($dynamic->data['dynamicSource'])->toBe('entrySection')
            ->and($dynamic->data['sectionId'])->toBe((int)$f['linked']->sectionId)
            ->and($dynamic->data['limit'])->toBe(5)
            ->and($dynamic->data['orderBy'])->toBe('title asc')
            ->and($dynamic->data['migratedMenuBuilder']['metadata']['mobile'])->toBe(['visibility' => 'both'])
            ->and($byTitle['Provider anchor']->type)->toBe(Custom::class)
            ->and($byTitle['Provider anchor']->getUrl())->toBe('#details')
            ->and($byTitle['Provider heading']->type)->toBe(Passive::class)
            ->and($byTitle['Provider fallback']->type)->toBe(Custom::class)
            ->and($byTitle['Provider fallback']->getUrl())->toBe('/fallback')
            ->and($byTitle['Provider hidden']->enabled)->toBeFalse();

        $separator = array_values(array_filter(
            $nodes,
            static fn(Node $node): bool => ($node->data['migratedMenuBuilder']['type'] ?? null) === 'separator',
        ))[0];
        expect($separator->type)->toBe(Passive::class);
    });
});

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
