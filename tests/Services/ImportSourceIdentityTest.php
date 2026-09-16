<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\ImportExportHelper as I;
use verbb\navigation\Navigation;
use verbb\navigation\nodetypes\Dynamic;

it('imports a Site link by its handle when destination database IDs differ', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    F::siteNode($menu, $site);
    $payload = I::generateMenuExport($menu);
    $oldId = $site->id;
    $handle = $site->handle;
    expect(Craft::$app->sites->deleteSite($site))->toBeTrue();
    $destination = F::secondarySite($handle);
    expect($destination->id)->not->toBe($oldId);
    $result = I::importMenuFromJson($payload);
    expect($result->errors)->toBe([]);
    $node = Node::find()->menuId($result->menu->id)->one();
    expect((int)$node->data['siteId'])->toBe($destination->id);
    expect($node->getUrl())->toBe(rtrim($destination->getBaseUrl(), '/'));
});

it('imports built-in Dynamic sources by handle instead of local IDs', function(string $kind) {
    $menu = F::menu();
    if ($kind === 'entrySection') {
        $source = F::entrySection();
        $field = 'sectionId';
        $sourceService = Craft::$app->entries;
        $save = fn($source) => $sourceService->saveSection($source);
        $create = fn($handle) => F::entrySection($handle);
    } elseif ($kind === 'categoryGroup') {
        $source = F::categoryGroup();
        $field = 'groupId';
        $save = fn($source) => Craft::$app->categories->saveGroup($source);
        $create = fn($handle) => F::categoryGroup($handle);
    } else {
        $fsHandle = 'portable'.bin2hex(random_bytes(4));
        $fsPath = '@webroot/uploads/' . $fsHandle;
        craft\helpers\FileHelper::createDirectory(Craft::getAlias($fsPath));
        $fs = new craft\fs\Local(['name' => $fsHandle, 'handle' => $fsHandle, 'path' => $fsPath, 'hasUrls' => true, 'url' => 'https://assets.test/']);
        if (!Craft::$app->fs->saveFilesystem($fs)) {
            throw new RuntimeException(json_encode($fs->getErrors()));
        }
        $create = function($handle) use ($fs) {
            $volume = new craft\models\Volume(['name' => $handle, 'handle' => $handle, 'fsHandle' => $fs->handle, 'subpath' => bin2hex(random_bytes(4))]);
            if (!Craft::$app->volumes->saveVolume($volume)) {
                throw new RuntimeException(json_encode($volume->getErrors()));
            }
            return $volume;
        };
        $source = $create('portable'.bin2hex(random_bytes(4)));
        $field = 'volumeId';
        $save = fn($source) => Craft::$app->volumes->saveVolume($source);
    }
    $node = new Node(['menuId' => $menu->id, 'siteId' => Craft::$app->sites->primarySite->id, 'type' => Dynamic::class, 'title' => 'Portable source', 'data' => ['dynamicSource' => $kind, $field => $source->id]]);
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    $payload = I::generateMenuExport($menu);
    $handle = $source->handle;
    $source->handle .= 'Foreign';
    $source->name .= ' Foreign';
    expect($save($source))->toBeTrue();
    // Retain the old ID as a different source, mirroring a destination ID collision.
    if ($kind === 'entrySection') {
        $type = Craft::$app->entries->getEntryTypesBySectionId($source->id)[0];
        $type->handle .= 'Foreign';
        $type->name .= ' Foreign';
        expect(Craft::$app->entries->saveEntryType($type))->toBeTrue();
    }
    $destination = $create($handle);
    expect($destination->id)->not->toBe($source->id);
    $result = I::importMenuFromJson($payload);
    expect($result->errors)->toBe([]);
    $imported = Node::find()->menuId($result->menu->id)->one();
    expect((int)$imported->data[$field])->toBe($destination->id);
    expect($imported->data['dynamicSource'])->toBe($kind);
})->with(['entrySection', 'categoryGroup', 'assetVolume']);

it('rolls back missing portable sources instead of using an unrelated numeric ID', function(string $action) {
    $menu = F::menu();
    $source = F::entrySection();
    $node = F::dynamicSectionNode($menu, $source);
    $payload = I::generateMenuExport($menu);
    $source->handle .= 'Other';
    expect(Craft::$app->entries->saveSection($source))->toBeTrue();
    $config = Craft::$app->projectConfig->get('navigation');
    $result = I::importMenuFromJson($payload, $action);
    expect($result->hasImportErrors())->toBeTrue();
    expect($result->nodesCreated)->toBe(0);
    expect(Node::find()->menuId($menu->id)->ids())->toBe([$node->id]);
    expect(Craft::$app->projectConfig->get('navigation'))->toBe($config);
})->with(['create', 'update']);

it('retains same-database imports from exports without source handles', function() {
    $menu = F::menu();
    $section = F::entrySection();
    F::dynamicSectionNode($menu, $section);
    $payload = I::generateMenuExport($menu);
    unset($payload['nodes'][0]['sourceHandle']);
    $result = I::importMenuFromJson($payload);
    expect($result->errors)->toBe([]);
    expect((int)Node::find()->menuId($result->menu->id)->one()->data['sectionId'])->toBe($section->id);
});

it('preserves data owned by other node types during portable exports', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Custom data', '/custom');
    $node->data = ['siteId' => 123456, 'dynamicSource' => 'entrySection', 'sectionId' => 654321];
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    $result = I::importMenuFromJson(I::generateMenuExport($menu));
    expect($result->errors)->toBe([]);
    expect(Node::find()->menuId($result->menu->id)->one()->data)->toBe($node->data);
});
