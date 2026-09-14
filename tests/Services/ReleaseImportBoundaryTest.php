<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\ImportExportHelper;
use verbb\navigation\records\NodeSiteSettings;
use yii\base\Event;

it('rolls back an import when a localized URL override is vetoed', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $node = F::customNode($menu, 'Original', '/original');
    $localized = Node::find()->id($node->id)->siteId($site->id)->status(null)->one();
    $localized->setUrl('/original-secondary');
    expect(Craft::$app->elements->saveElement($localized, true, false))->toBeTrue();
    $payload = ImportExportHelper::generateMenuExport($menu);
    $payload['menu']['name'] = 'Replacement name';
    $payload['nodes'][0]['title'] = 'Replacement';
    $payload['nodes'][0]['siteOverrides'][$site->handle]['url'] = '/rejected-secondary';
    $config = Craft::$app->projectConfig->get('navigation');
    $vetoed = false;
    $veto = static function($event) use (&$vetoed) {
        if ($event->sender->url === '/rejected-secondary') {
            $vetoed = true;
            $event->isValid = false;
        }
    };
    Event::on(NodeSiteSettings::class, NodeSiteSettings::EVENT_BEFORE_INSERT, $veto);
    Event::on(NodeSiteSettings::class, NodeSiteSettings::EVENT_BEFORE_UPDATE, $veto);
    try {
        $result = ImportExportHelper::importMenuFromJson($payload, 'update');
    } finally {
        Event::off(NodeSiteSettings::class, NodeSiteSettings::EVENT_BEFORE_INSERT, $veto);
        Event::off(NodeSiteSettings::class, NodeSiteSettings::EVENT_BEFORE_UPDATE, $veto);
    }

    expect($vetoed)->toBeTrue();
    expect($result->hasImportErrors())->toBeTrue();
    expect($result->nodesCreated)->toBe(0);
    expect(Node::find()->menuId($menu->id)->ids())->toBe([$node->id]);
    expect(Node::find()->id($node->id)->one()->title)->toBe('Original');
    expect(Node::find()->id($node->id)->siteId($site->id)->one()->getUrl())->toBe('/original-secondary');
    expect(Craft::$app->projectConfig->get('navigation'))->toEqual($config);
});

it('reports scalar JSON imports as validation errors', function(string $json) {
    $result = ImportExportHelper::importMenuFromJson($json);
    expect($result->hasImportErrors())->toBeTrue();
    expect($result->nodesCreated)->toBe(0);
    expect($result->menu)->toBeNull();
})->with(['null', 'true', '42', '"menu"']);
