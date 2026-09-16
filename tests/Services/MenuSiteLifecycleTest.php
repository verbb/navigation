<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation as N;

function setAuditMenuSiteEnabled(int $menuId, int $siteId, bool $enabled): void
{
    $menu = N::$plugin->getMenus()->getMenuById($menuId);
    $settings = $menu->getSiteSettings();
    $settings[$siteId]->enabled = $enabled;
    $menu->setSiteSettings($settings);
    expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
}

it('stops returning public nodes immediately when their menu site is disabled', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $node = F::customNode($menu, 'Site-specific menu link', '/site-specific');
    W::withAbsoluteUrl('https://menu-sites.test/', function() use ($menu, $site, $node) {
        $query = static fn() => Node::find()->menuId($menu->id)->siteId($site->id);
        expect($query()->all())->toHaveCount(1);
        expect($query()->all())->toHaveCount(1);
        setAuditMenuSiteEnabled($menu->id, $site->id, false);

        expect($query()->all())->toBe([]);
        expect($query()->count())->toBe(0);
        expect($query()->ids())->toBe([]);
        expect($query()->asArray()->all())->toBe([]);
        expect($query()->status(null)->all())->toHaveCount(1);
        expect(Node::find()->menuId($menu->id)->site('*')->all())->toHaveCount(1);
        expect(Node::find()->id($node->id)->siteId($node->siteId)->one()?->id)->toBe($node->id);

        setAuditMenuSiteEnabled($menu->id, $site->id, true);
        expect($query()->all())->toHaveCount(1);
    });
});

it('omits a disabled menu site from GraphQL while preserving the enabled locale', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    F::customNode($menu, 'GraphQL menu link', '/graphql-site');
    setAuditMenuSiteEnabled($menu->id, $site->id, false);
    $schema = craft\helpers\Gql::createFullAccessSchema();
    $query = 'query($handle: String!, $site: [QueryArgument]) { navigationNodes(menuHandle: $handle, siteId: $site) { id title } }';
    foreach ([$site->id => 0, Craft::$app->getSites()->getPrimarySite()->id => 1] as $siteId => $count) {
        $result = Craft::$app->getGql()->executeQuery($schema, $query, ['handle' => $menu->handle, 'site' => [$siteId]]);
        expect($result['errors'] ?? [])->toBe([]);
        expect($result['data']['navigationNodes'])->toHaveCount($count);
    }
});

it('does not retain deleted-source flags after a disabled menu locale is re-enabled', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    expect(Craft::$app->getElements()->deleteElement($entry))->toBeTrue();
    setAuditMenuSiteEnabled($menu->id, $site->id, false);
    expect(Craft::$app->getElements()->restoreElement($entry))->toBeTrue();
    setAuditMenuSiteEnabled($menu->id, $site->id, true);
    $canonical = Node::find()->id($node->id)->status(null)->one();
    expect(Craft::$app->getElements()->saveElement($canonical))->toBeTrue();
    $localized = Node::find()->id($node->id)->siteId($site->id)->status(null)->one();
    expect($localized?->getIsDisabledByLinkedElement())->toBeFalse();

    expect(Craft::$app->getElements()->deleteElement($entry))->toBeTrue();
    expect(Node::find()->id($node->id)->site('*')->all())->toBe([]);
    expect(Craft::$app->getElements()->restoreElement($entry))->toBeTrue();
    expect(Node::find()->id($node->id)->site('*')->all())->toHaveCount(2);
});

it('preserves a disabled locale when a menu temporarily supports one site during source restoration', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $node->setEnabledForSite(false);
    expect(Craft::$app->getElements()->saveElement($node, true, false))->toBeTrue();
    expect(Craft::$app->getElements()->deleteElement($entry))->toBeTrue();
    setAuditMenuSiteEnabled($menu->id, $site->id, false);
    expect(Craft::$app->getElements()->restoreElement($entry))->toBeTrue();

    expect(Node::find()->id($node->id)->one())->toBeNull();
});

it('recovers linked state when every menu site was disabled during source restoration', function(bool $bothSites) {
    $site = F::secondarySite();
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $localized = Node::find()->id($node->id)->siteId($site->id)->status(null)->one();
    $localized->setEnabledForSite(false);
    expect(Craft::$app->getElements()->saveElement($localized, true, false))->toBeTrue();
    expect(Craft::$app->getElements()->deleteElement($entry))->toBeTrue();
    setAuditMenuSiteEnabled($menu->id, $site->id, false);
    setAuditMenuSiteEnabled($menu->id, $node->siteId, false);
    expect(Craft::$app->getElements()->restoreElement($entry))->toBeTrue();
    $menu = N::$plugin->getMenus()->getMenuById($menu->id);
    $settings = $menu->getSiteSettings();
    $settings[$node->siteId]->enabled = true;
    $settings[$site->id]->enabled = $bothSites;
    $menu->setSiteSettings($settings);
    expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    $canonical = Node::find()->id($node->id)->siteId($node->siteId)->status(null)->one();
    expect($canonical->getIsDisabledByLinkedElement())->toBeFalse();
    expect(Node::find()->id($node->id)->one()?->id)->toBe($node->id);
    if ($bothSites) {
        $localized = Node::find()->id($node->id)->siteId($site->id)->status(null)->one();
        expect($localized->getIsDisabledByLinkedElement())->toBeFalse();
        expect($localized->getEnabledForSite())->toBeFalse();
    }
})->with([false, true]);

it('preserves disabled menu settings when a linked node vetoes reactivation', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    expect(Craft::$app->getElements()->deleteElement($entry))->toBeTrue();
    setAuditMenuSiteEnabled($menu->id, $node->siteId, false);
    expect(Craft::$app->getElements()->restoreElement($entry))->toBeTrue();
    $veto = static function($event) use ($node) {
        if ($event->sender->id === $node->id) {
            $event->isValid = false;
        }
    };
    yii\base\Event::on(Node::class, Node::EVENT_BEFORE_SAVE, $veto);
    try {
        expect(fn() => setAuditMenuSiteEnabled($menu->id, $node->siteId, true))->toThrow(yii\base\UserException::class);
    } finally {
        yii\base\Event::off(Node::class, Node::EVENT_BEFORE_SAVE, $veto);
    }
    $savedMenu = N::$plugin->getMenus()->getMenuById($menu->id);
    expect($savedMenu->getSiteIds())->toBe([]);
    $savedNode = Node::find()->id($node->id)->status(null)->one();
    expect($savedNode->enabled)->toBeFalse();
    expect($savedNode->getIsDisabledByLinkedElement())->toBeTrue();
});

it('queues propagation to recreate node variants when a menu site is re-enabled', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $node = F::customNode($menu, 'Reactivated locale', '/reactivated');
    setAuditMenuSiteEnabled($menu->id, $node->siteId, false);
    $localized = Node::find()->id($node->id)->siteId($site->id)->status(null)->one();
    expect(Craft::$app->getElements()->saveElement($localized))->toBeTrue();
    expect(Node::find()->id($node->id)->siteId($node->siteId)->status(null)->one())->toBeNull();
    $jobs = [];
    $capture = static function($event) use ($menu, &$jobs) {
        if ($event->job instanceof craft\queue\jobs\ResaveElements
            && $event->job->elementType === Node::class
            && ($event->job->criteria['menuId'] ?? null) === $menu->id) {
            $jobs[] = $event->job;
            $event->handled = true;
        }
    };
    $queue = Craft::$app->getQueue();
    $queue->on(yii\queue\Queue::EVENT_BEFORE_PUSH, $capture);
    try {
        setAuditMenuSiteEnabled($menu->id, $node->siteId, true);
        expect($jobs)->toHaveCount(1);
    } finally {
        $queue->off(yii\queue\Queue::EVENT_BEFORE_PUSH, $capture);
    }
    $jobId = $queue->push($jobs[0]);
    expect($queue->executeJob($jobId))->toBeTrue();
    expect(Node::find()->id($node->id)->siteId($node->siteId)->one()?->id)->toBe($node->id);
    expect(Node::find()->id($node->id)->siteId($site->id)->one()?->id)->toBe($node->id);
});
