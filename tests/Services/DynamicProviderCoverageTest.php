<?php

declare(strict_types=1);

use craft\elements\Entry;
use craft\models\Section;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation;

it('projects only structure roots from an entry section', function(string $orderBy) {
    $section = F::entrySection();
    $section->type = Section::TYPE_STRUCTURE;
    $section->maxLevels = 3;
    expect(Craft::$app->getEntries()->saveSection($section))->toBeTrue();
    $section = Craft::$app->getEntries()->getSectionById($section->id);
    [$first, $child, $second, $third] = F::entries(4, $section);
    expect(Craft::$app->getStructures()->append($section->structureId, $child, $first))->toBeTrue();
    expect((int)Entry::find()->id($child->id)->one()->level)->toBe(2);
    // Deliberately differ from title and creation order in either direction.
    expect(Craft::$app->getStructures()->moveBefore($section->structureId, $second, $first))->toBeTrue();

    $menu = F::menu();
    $dynamic = F::dynamicSectionNode($menu, $section, null, ['orderBy' => $orderBy]);
    $children = Navigation::$plugin->getDynamicSources()->getProjectedChildren($dynamic, $dynamic->siteId);
    expect(array_map(static fn($node) => (int)$node->getElement()->id, $children))
        ->toBe([(int)$second->id, (int)$first->id, (int)$third->id]);
})->with(['default', 'structure']);

it('projects assets from the selected volume without including another volume', function() {
    $menu = F::menu();
    $handle = 'navigationTestAssets' . uniqid();
    $path = Craft::getAlias('@webroot') . '/' . $handle;
    $fs = new \craft\fs\Local(['name' => 'Projection files', 'handle' => $handle, 'path' => $path, 'hasUrls' => true, 'url' => 'https://files.test/']);
    expect(Craft::$app->getFs()->saveFilesystem($fs))->toBeTrue(json_encode($fs->getErrors()));
    $volumes = [];
    try {
        foreach (['Selected', 'Other'] as $label) {
            $volume = new \craft\models\Volume(['name' => $label, 'handle' => $handle . $label, 'fsHandle' => $fs->handle, 'subpath' => $label]);
            expect(Craft::$app->getVolumes()->saveVolume($volume))->toBeTrue();
            $volumes[] = $volume;
            $temporary = tempnam(sys_get_temp_dir(), 'navigation-projection-');
            file_put_contents($temporary, $label . ' download');
            $asset = new \craft\elements\Asset([
                'volumeId' => $volume->id,
                'newFolderId' => Craft::$app->getAssets()->getRootFolderByVolumeId($volume->id)->id,
                'tempFilePath' => $temporary,
                'filename' => strtolower($label) . '.txt',
                'title' => $label . ' download',
            ]);
            expect(Craft::$app->getElements()->saveElement($asset))->toBeTrue();
            if ($label === 'Selected') {
                $selected = $asset;
            }
        }
        $node = new \verbb\navigation\elements\Node([
            'menuId' => $menu->id,
            'siteId' => Craft::$app->getSites()->getPrimarySite()->id,
            'type' => \verbb\navigation\nodetypes\Dynamic::class,
            'title' => 'Downloads',
            'data' => ['dynamicSource' => 'assetVolume', 'volumeId' => (string)$volumes[0]->id],
        ]);
        expect(Craft::$app->getElements()->saveElement($node))->toBeTrue();
        $children = Navigation::$plugin->getDynamicSources()->getProjectedChildren($node, $node->siteId);
        expect(array_map(static fn($item) => $item->elementId, $children))->toBe([(int)$selected->id]);
        expect($children[0]->title)->toBe('Selected download');
        expect($children[0]->getUrl())->toBe('https://files.test/Selected/selected.txt');
    } finally {
        foreach ($volumes as $volume) {
            Craft::$app->getVolumes()->deleteVolume($volume);
        }
        Craft::$app->getFs()->removeFilesystem($fs);
        \craft\helpers\FileHelper::removeDirectory($path);
    }
});
