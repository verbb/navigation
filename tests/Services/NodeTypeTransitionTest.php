<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation;
use verbb\navigation\nodetypes\Asset;
use verbb\navigation\nodetypes\Category;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Dynamic;
use verbb\navigation\nodetypes\Entry;
use verbb\navigation\nodetypes\GroupColumn;
use verbb\navigation\nodetypes\Passive;
use verbb\navigation\nodetypes\Site;

it('never retains a linked element in another type picker and clears links on non-element saves', function() {
    $menu = NavigationFixtureFactory::menu();
    $entry = NavigationFixtureFactory::entries(1)[0];
    $category = NavigationFixtureFactory::categories(1)[0];
    $targets = array_map(static fn($type) => $type::class, Navigation::$plugin->getNodeTypes()->getRegisteredNodeTypes());

    // Every target gets a fresh persisted node so the matrix covers the same
    // cached-element state as Craft's existing-node slideout.
    foreach ([$entry, $category] as $source) {
        foreach ($targets as $target) {
            $node = NavigationFixtureFactory::elementNode($menu, $source);
            $sourceType = $node->type;
            expect($node->getElement()?->id)->toBe($source->id);

            $node->type = $target;
            $expected = $target === $sourceType ? $source->id : null;
            expect($node->getElement()?->id)->toBe($expected, "$sourceType → $target must not display an incompatible element");

            if (!$node->isElement() && $target !== Dynamic::class) {
                expect(Craft::$app->getElements()->saveElement($node))->toBeTrue(json_encode($node->getErrors()));
                $reloaded = Node::find()->id($node->id)->siteId($node->siteId)->status(null)->one();
                expect($reloaded?->elementId)->toBeNull();
                expect($reloaded?->getElement())->toBeNull();
                $reloaded->type = $sourceType;
                expect($reloaded->getElement())->toBeNull("$sourceType → $target → $sourceType must not restore the discarded selection");
            }
        }
    }
});

it('allows an empty intermediate element picker in a draft but requires a link on publication', function() {
    $menu = NavigationFixtureFactory::menu();
    $entry = NavigationFixtureFactory::entries(1)[0];
    $node = NavigationFixtureFactory::elementNode($menu, $entry);
    $node->type = Asset::class;
    $node->setLinkedElementId(null);
    $node->draftId = 1;
    expect($node->getIsDraft())->toBeTrue();

    $assetType = $node->nodeType();
    expect($assetType->node)->toBe($node);
    expect($assetType->node->getIsDraft())->toBeTrue();
    expect($assetType->beforeSaveNode(false))->toBeTrue(json_encode($node->getErrors()));

    $node->type = Entry::class;
    expect($node->nodeType()->beforeSaveNode(false))->toBeTrue();
    expect($node->getElement())->toBeNull();

    $node->draftId = null;
    expect($node->nodeType()->beforeSaveNode(false))->toBeFalse();
    expect($node->getErrors('linkedElementId'))->not->toBeEmpty();

    $node->setLinkedElementId($entry->id);
    expect($node->getElement()?->id)->toBe($entry->id);
    expect($node->nodeType()->beforeSaveNode(false))->toBeTrue();
});

it('persists an empty type picker in a real draft without changing the published node', function($emptySelection) {
    $menu = NavigationFixtureFactory::menu();
    $entry = NavigationFixtureFactory::entries(1)[0];
    $category = NavigationFixtureFactory::categories(1)[0];
    $node = NavigationFixtureFactory::elementNode($menu, $entry);
    $draft = Craft::$app->getDrafts()->createDraft($node);
    $draft->type = Category::class;
    $draft->setLinkedElementId($emptySelection);
    expect(Craft::$app->getElements()->saveElement($draft))->toBeTrue(json_encode($draft->getErrors()));

    $reloaded = Node::find()->id($draft->id)->drafts()->status(null)->one();
    expect($reloaded?->type)->toBe(Category::class);
    expect($reloaded?->getElement())->toBeNull();
    expect(Node::find()->id($node->id)->one()?->elementId)->toBe($entry->id);

    $reloaded->setLinkedElementId($category->id);
    expect(Craft::$app->getElements()->saveElement($reloaded))->toBeTrue(json_encode($reloaded->getErrors()));
    $published = Craft::$app->getDrafts()->applyDraft($reloaded);
    $canonical = Node::find()->id($node->id)->one();
    expect($published->id)->toBe($node->id);
    expect($canonical?->type)->toBe(Category::class);
    expect($canonical?->getElement()?->id)->toBe($category->id);
})->with(['null selection' => [null], 'empty selector array' => [[]]]);

it('keeps empty linked pickers empty across changes from every built-in non-element source', function() {
    $menu = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    $sources = [
        fn() => NavigationFixtureFactory::customNode($menu, 'Custom', '/custom'),
        fn() => NavigationFixtureFactory::passiveNode($menu, 'Passive'),
        fn() => NavigationFixtureFactory::groupColumnNode($menu, 'Column'),
        fn() => NavigationFixtureFactory::siteNode($menu, Craft::$app->getSites()->getPrimarySite()),
        fn() => NavigationFixtureFactory::dynamicSectionNode($menu, $section),
    ];
    $targets = array_map(static fn($type) => $type::class, Navigation::$plugin->getNodeTypes()->getRegisteredNodeTypes());

    foreach ($sources as $createSource) {
        foreach ($targets as $target) {
            $node = $createSource();
            $sourceType = $node->type;
            expect($node->getElement())->toBeNull();
            $node->type = $target;
            expect($node->getElement())->toBeNull("$sourceType → $target must have an empty picker");

            if ($node->isElement()) {
                expect($node->nodeType()->beforeSaveNode(false))->toBeFalse();
                $node->draftId = 1;
                expect($node->nodeType()->beforeSaveNode(false))->toBeTrue();
            }
        }
    }
});

it('clears a real asset selection when changing to any other installed node type', function() {
    $menu = NavigationFixtureFactory::menu();
    $handle = 'nodeTransitionAssets' . uniqid();
    $path = Craft::getAlias('@webroot') . '/' . $handle;
    $filesystem = new \craft\fs\Local(['name' => 'Transition files', 'handle' => $handle, 'path' => $path, 'hasUrls' => true, 'url' => 'https://files.test/']);
    expect(Craft::$app->getFs()->saveFilesystem($filesystem))->toBeTrue();
    $volume = new \craft\models\Volume(['name' => 'Transition volume', 'handle' => $handle, 'fsHandle' => $handle]);

    try {
        expect(Craft::$app->getVolumes()->saveVolume($volume))->toBeTrue();
        $temporary = tempnam(sys_get_temp_dir(), 'navigation-transition-');
        file_put_contents($temporary, 'Transition asset');
        $asset = new \craft\elements\Asset([
            'volumeId' => $volume->id,
            'newFolderId' => Craft::$app->getAssets()->getRootFolderByVolumeId($volume->id)->id,
            'tempFilePath' => $temporary,
            'filename' => 'transition.txt',
            'title' => 'Transition asset',
        ]);
        expect(Craft::$app->getElements()->saveElement($asset))->toBeTrue();

        foreach (Navigation::$plugin->getNodeTypes()->getRegisteredNodeTypes() as $targetType) {
            $node = NavigationFixtureFactory::elementNode($menu, $asset);
            expect($node->getElement()?->id)->toBe($asset->id);
            $node->type = $targetType::class;
            expect($node->getElement()?->id)->toBe($targetType::class === Asset::class ? $asset->id : null);
        }
    } finally {
        if ($volume->id) {
            Craft::$app->getVolumes()->deleteVolume($volume);
        }
        Craft::$app->getFs()->removeFilesystem($filesystem);
        \craft\helpers\FileHelper::removeDirectory($path);
    }
});
