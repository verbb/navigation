<?php

use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\User;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\helpers\ElementPickerHelper;
use verbb\navigation\Navigation as N;
use verbb\navigation\nodetypes\Asset as AssetNodeType;
use verbb\navigation\nodetypes\Category as CategoryNodeType;

it('limits category picker sources and results to viewable groups', function(bool $granted) {
    $menu = F::menu();
    $allowed = F::categoryGroup();
    $hidden = F::categoryGroup();
    $category = F::categories(1, $allowed)[0];
    $hiddenCategory = F::categories(1, $hidden)[0];
    $editor = new User(['username' => uniqid('category-picker-'), 'email' => uniqid('category-picker-') . '@example.test']);
    expect(Craft::$app->getElements()->saveElement($editor))->toBeTrue();

    boundaryRequest(function() use ($menu, $allowed, $hidden, $category, $hiddenCategory, $editor, $granted) {
        Craft::$app->set('userPermissions', new craft\services\UserPermissions());
        expect(Craft::$app->getUserPermissions()->saveUserPermissions($editor->id, $granted ? ['viewCategories:' . $allowed->uid] : []))->toBeTrue();
        Craft::$app->getUser()->setIdentity($editor);
        $sources = ElementPickerHelper::filterSourcesForUser(Category::class, '*');
        expect($sources)->not->toContain('group:' . $hidden->uid);
        expect(ElementPickerHelper::filterSourcesForUser(Category::class, ['group:' . $hidden->uid]))->toBe([]);
        if ($granted) {
            expect($sources)->toContain('group:' . $allowed->uid);
        } else {
            expect($sources)->toBe([]);
            expect(array_column(N::$plugin->getMenus()->getBuilderTabs($menu), 'elementType'))->not->toContain(Category::class);
        }
        $picker = ElementPickerHelper::getPickerConfig([], CategoryNodeType::class, Category::class);
        $query = Category::find()->id([$category->id, $hiddenCategory->id]);
        Craft::configure($query, $picker['criteria'] ?? []);
        expect(array_map('intval', $query->ids()))->toBe($granted ? [$category->id] : []);
    });
})->with([false, true]);

it('limits asset picker sources and results by volume and uploader permissions', function(string $access) {
    $menu = F::menu();
    $editor = new User(['username' => uniqid('asset-picker-'), 'email' => uniqid('asset-picker-') . '@example.test']);
    expect(Craft::$app->getElements()->saveElement($editor))->toBeTrue();
    $filesystems = [];
    $volumes = [];
    $paths = [];
    $assets = [];

    try {
        foreach (['allowed', 'hidden'] as $scope) {
            $handle = 'picker' . uniqid();
            $path = Craft::getAlias('@webroot') . '/' . $handle;
            $fs = new craft\fs\Local(['name' => $handle, 'handle' => $handle, 'path' => $path, 'hasUrls' => true, 'url' => 'https://files.test/']);
            expect(Craft::$app->getFs()->saveFilesystem($fs))->toBeTrue();
            $filesystems[] = $fs;
            $paths[] = $path;
            $volume = new craft\models\Volume(['name' => $handle, 'handle' => $handle, 'fsHandle' => $handle]);
            expect(Craft::$app->getVolumes()->saveVolume($volume))->toBeTrue();
            $volumes[$scope] = $volume;
            foreach (['own', 'peer'] as $owner) {
                $folder = Craft::$app->getAssets()->ensureFolderByFullPathAndVolume('downloads/guides', $volume, false);
                $temporary = tempnam(sys_get_temp_dir(), 'navigation-picker-');
                file_put_contents($temporary, 'Picker fixture');
                $asset = new Asset([
                    'volumeId' => $volume->id, 'newFolderId' => $folder->id,
                    'tempFilePath' => $temporary, 'filename' => $owner . '.txt', 'title' => $owner,
                    'uploaderId' => $owner === 'own' ? $editor->id : User::find()->admin()->one()->id,
                ]);
                expect(Craft::$app->getElements()->saveElement($asset))->toBeTrue();
                $assets[$scope][$owner] = $asset;
            }
        }
        boundaryRequest(function() use ($menu, $editor, $access, $volumes, $assets) {
            Craft::$app->set('userPermissions', new craft\services\UserPermissions());
            $grants = $access === 'none' ? [] : ['viewAssets:' . $volumes['allowed']->uid];
            if ($access === 'peers') {
                $grants[] = 'viewPeerAssets:' . $volumes['allowed']->uid;
            }
            expect(Craft::$app->getUserPermissions()->saveUserPermissions($editor->id, $grants))->toBeTrue();
            Craft::$app->getUser()->setIdentity($editor);
            $sources = ElementPickerHelper::filterSourcesForUser(Asset::class, '*');
            expect($sources)->not->toContain('volume:' . $volumes['hidden']->uid);
            expect(ElementPickerHelper::filterSourcesForUser(Asset::class, ['volume:' . $volumes['hidden']->uid]))->toBe([]);
            if ($access !== 'none') {
                expect($sources)->toContain('volume:' . $volumes['allowed']->uid);
            } else {
                expect($sources)->toBe([]);
                expect(array_column(N::$plugin->getMenus()->getBuilderTabs($menu), 'elementType'))->not->toContain(Asset::class);
            }
            $picker = ElementPickerHelper::getPickerConfig([], AssetNodeType::class, Asset::class);
            $ids = array_map(fn($asset) => $asset->id, [...array_values($assets['allowed']), ...array_values($assets['hidden'])]);
            $query = Asset::find()->id($ids)->orderBy('elements.id');
            Craft::configure($query, $picker['criteria'] ?? []);
            $expected = match ($access) {
                'none' => [],
                'own' => [$assets['allowed']['own']->id],
                'peers' => [$assets['allowed']['own']->id, $assets['allowed']['peer']->id],
            };
            expect(array_map('intval', $query->ids()))->toBe($expected);
        });
    } finally {
        foreach ($volumes as $volume) {
            Craft::$app->getVolumes()->deleteVolume($volume);
        }
        foreach ($filesystems as $fs) {
            Craft::$app->getFs()->removeFilesystem($fs);
        }
        foreach ($paths as $path) {
            craft\helpers\FileHelper::removeDirectory($path);
        }
    }
})->with(['none', 'own', 'peers']);
