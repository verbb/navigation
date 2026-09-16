<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\ImportExportHelper;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\GroupColumn;
use verbb\navigation\nodetypes\Passive;
use verbb\navigation\nodetypes\Entry;
use verbb\navigation\controllers\NodesController;
use verbb\navigation\Navigation;

it('retains a zero title when saving and backing up authored nodes', function(string $type) {
    $menu = F::menu();
    $node = new Node([
        'menuId' => $menu->id,
        'siteId' => Craft::$app->sites->primarySite->id,
        'type' => $type,
        'title' => '0',
        'url' => '/zero',
        'enabled' => true,
    ]);
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    expect(Node::find()->id($node->id)->one()->title)->toBe('0');

    $import = ImportExportHelper::importMenuFromJson(ImportExportHelper::generateMenuExport($menu));
    expect($import->errors)->toBe([]);
    expect(Node::find()->menuId($import->menu->id)->one()->title)->toBe('0');
})->with([Custom::class, Passive::class, GroupColumn::class]);

it('retains a zero linked-title override through draft publication and source updates', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $draft = Craft::$app->drafts->createDraft($node);
    $draft->title = '0';
    expect(Craft::$app->elements->saveElement($draft))->toBeTrue();
    $draft = Node::find()->id($draft->id)->drafts()->status(null)->one();
    expect($draft->title)->toBe('0');
    Craft::$app->drafts->applyDraft($draft);
    expect(Node::find()->id($node->id)->one()->title)->toBe('0');

    $entry->title = 'Renamed source';
    expect(Craft::$app->elements->saveElement($entry))->toBeTrue();
    expect(Node::find()->id($node->id)->one()->title)->toBe('0');
});

it('retains a zero linked-title override submitted to the add-node controller', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    boundaryRequest(function() use ($menu, $entry) {
        $payload = boundaryAddPayload($menu, '0');
        $payload['type'] = Entry::class;
        $payload['elementId'] = $entry->id;
        Craft::$app->request->setBodyParams(['nodes' => [$payload]]);
        $response = (new NodesController('nodes', Navigation::$plugin))->actionAddNodes();
        expect($response->statusCode)->toBe(200);
        expect(Node::find()->menuId($menu->id)->status(null)->one()->title)->toBe('0');
    });
});

it('still supplies the linked title when the authored title is blank', function(?string $title) {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $node->title = $title;
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    expect(Node::find()->id($node->id)->one()->title)->toBe($entry->title);
})->with(['empty' => [''], 'null' => [null]]);
