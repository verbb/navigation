<?php

declare(strict_types=1);

namespace Tests\General;

use Craft;
use PHPUnit\Framework\TestCase as BaseTestCase;
use RuntimeException;
use verbb\navigation\Navigation;

abstract class TestCase extends BaseTestCase
{
    private array $existingMenuIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureCraftBootstrapped();
        $this->existingMenuIds = array_map('intval', (new \craft\db\Query())
            ->select('id')->from('{{%navigation_menus}}')->column());
        Craft::$app->getSites()->refreshSites();
        Craft::$app->getIsMultiSite(true);
        Craft::$app->getIsMultiSite(true, true);
    }

    protected function tearDown(): void
    {
        if (class_exists(Craft::class) && Craft::$app?->getIsInstalled()) {
            // New-site tests must not propagate hundreds of menus left by
            // unrelated cases. Preserve any fixtures present before this test.
            $menus = Navigation::$plugin->getMenus();
            $menus->resetCache();
            foreach ($menus->getAllMenus() as $menu) {
                if (!in_array((int)$menu->id, $this->existingMenuIds, true) && !$menus->deleteMenu($menu)) {
                    throw new RuntimeException('Could not remove a test menu.');
                }
            }

            // Each test models a separate request. Clearing only the active
            // schema retains compiled query fields from an earlier schema.
            Craft::$app->getGql()->flushCaches();
            // Craft's flush leaves field arguments alive; a fresh service must
            // not reuse scalar types from the discarded registry.
            Craft::$app->set('gql', new \craft\services\Gql());

            // Multisite fixtures belong to their test, not to later GraphQL scopes.
            \Tests\Support\ResetTestDatabase::pruneFixtureSites();
            Craft::$app->getIsMultiSite(true);
            Craft::$app->getIsMultiSite(true, true);
            Craft::$app->getSites()->setCurrentSite(Craft::$app->getSites()->getPrimarySite());
        }

        parent::tearDown();
    }

    protected function ensureCraftBootstrapped(): void
    {
        if (!class_exists(Craft::class) || !Craft::$app) {
            throw new RuntimeException('Craft application must be bootstrapped before running integration tests.');
        }
    }
}
