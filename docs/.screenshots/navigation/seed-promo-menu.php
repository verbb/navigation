use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\fs\Local;
use craft\models\CategoryGroup;
use craft\models\CategoryGroup_SiteSettings;
use craft\models\EntryType;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use craft\models\Site;
use craft\models\Volume;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\NodeTypeHelper;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\models\MenuSiteSettings;
use verbb\navigation\Navigation;
use verbb\navigation\nodetypes\Custom;

const DOCS_SCREENSHOT_MENU_HANDLE = 'docsScreenshotMainMenu';
const DOCS_SCREENSHOT_SECTION_HANDLE = 'docsScreenshotGin';
const DOCS_SCREENSHOT_CATEGORY_GROUP_HANDLE = 'docsScreenshotSpirits';
const DOCS_SCREENSHOT_VOLUME_HANDLE = 'docsScreenshotAssets';
const DOCS_SCREENSHOT_FS_HANDLE = 'docsScreenshotAssets';

function docsScreenshotSite(): Site
{
    return Craft::$app->getSites()->getPrimarySite();
}

function docsScreenshotMenu(): MenuSettings
{
    $menus = Navigation::$plugin->getMenus();
    $menu = $menus->getMenuByHandle(DOCS_SCREENSHOT_MENU_HANDLE);

    if (!$menu) {
        $menu = new MenuSettings([
            'name' => 'Spirits Menu',
            'handle' => DOCS_SCREENSHOT_MENU_HANDLE,
        ]);
    }

    $menu->setSiteSettings(array_map(
        static fn(Site $site): MenuSiteSettings => new MenuSiteSettings([
            'siteId' => $site->id,
            'enabled' => true,
        ]),
        Craft::$app->getSites()->getAllSites(),
    ));

    if (!$menus->saveMenu($menu)) {
        throw new RuntimeException('Unable to save docs screenshot menu: ' . json_encode($menu->getErrors()));
    }

    $savedMenu = $menus->getMenuByHandle(DOCS_SCREENSHOT_MENU_HANDLE);

    if (!$savedMenu) {
        throw new RuntimeException('Docs screenshot menu could not be reloaded.');
    }

    return $savedMenu;
}

function docsScreenshotClearMenuNodes(MenuSettings $menu): void
{
    $nodes = Node::find()
        ->menuId($menu->id)
        ->status(null)
        ->all();

    foreach ($nodes as $node) {
        Craft::$app->getElements()->deleteElement($node);
    }
}

function docsScreenshotSection(string $handle, string $name): Section
{
    $entriesService = Craft::$app->getEntries();
    $section = $entriesService->getSectionByHandle($handle);

    if ($section) {
        return $section;
    }

    $entryTypeHandle = $handle . 'Type';
    $entryType = new EntryType([
        'name' => $name . ' Type',
        'handle' => $entryTypeHandle,
        'hasTitleField' => true,
    ]);

    if (!$entriesService->saveEntryType($entryType)) {
        throw new RuntimeException('Unable to save docs screenshot entry type: ' . json_encode($entryType->getErrors()));
    }

    $section = new Section([
        'name' => $name,
        'handle' => $handle,
        'type' => Section::TYPE_CHANNEL,
    ]);
    $section->setEntryTypes([$entryType]);
    $section->setSiteSettings(array_map(
        static fn(Site $site): Section_SiteSettings => new Section_SiteSettings([
            'siteId' => $site->id,
            'enabledByDefault' => true,
            'hasUrls' => true,
            'uriFormat' => $handle . '/{slug}',
            'template' => '_docs-screenshot/entry',
        ]),
        Craft::$app->getSites()->getAllSites(),
    ));

    if (!$entriesService->saveSection($section)) {
        throw new RuntimeException('Unable to save docs screenshot section: ' . json_encode($section->getErrors()));
    }

    $savedSection = $entriesService->getSectionByHandle($handle);

    if (!$savedSection) {
        throw new RuntimeException("Docs screenshot section `{$handle}` could not be reloaded.");
    }

    return $savedSection;
}

function docsScreenshotEntry(Section $section, string $title, string $slug): Entry
{
    $site = docsScreenshotSite();
    $existing = Entry::find()
        ->sectionId($section->id)
        ->siteId($site->id)
        ->slug($slug)
        ->one();

    if ($existing) {
        if ($existing->title !== $title) {
            $existing->title = $title;
            Craft::$app->getElements()->saveElement($existing);
        }

        return $existing;
    }

    $entryType = Craft::$app->getEntries()->getEntryTypesBySectionId($section->id)[0] ?? null;

    if (!$entryType) {
        throw new RuntimeException("Section `{$section->handle}` has no entry types.");
    }

    $entry = new Entry([
        'sectionId' => $section->id,
        'typeId' => $entryType->id,
        'siteId' => $site->id,
        'title' => $title,
        'slug' => $slug,
        'postDate' => new DateTime(),
        'enabled' => true,
    ]);

    if (!Craft::$app->getElements()->saveElement($entry)) {
        throw new RuntimeException("Unable to save entry `{$title}`: " . json_encode($entry->getErrors()));
    }

    return $entry;
}

function docsScreenshotCategoryGroup(string $handle, string $name): CategoryGroup
{
    $groupsService = Craft::$app->getCategories();
    $group = $groupsService->getGroupByHandle($handle);

    if ($group) {
        return $group;
    }

    $group = new CategoryGroup([
        'name' => $name,
        'handle' => $handle,
        'maxLevels' => null,
    ]);
    $group->setSiteSettings(array_map(
        static fn(Site $site): CategoryGroup_SiteSettings => new CategoryGroup_SiteSettings([
            'siteId' => $site->id,
            'hasUrls' => true,
            'uriFormat' => $handle . '/{slug}',
            'template' => '_docs-screenshot/category',
        ]),
        Craft::$app->getSites()->getAllSites(),
    ));

    if (!$groupsService->saveGroup($group)) {
        throw new RuntimeException('Unable to save docs screenshot category group: ' . json_encode($group->getErrors()));
    }

    $savedGroup = $groupsService->getGroupByHandle($handle);

    if (!$savedGroup) {
        throw new RuntimeException("Docs screenshot category group `{$handle}` could not be reloaded.");
    }

    return $savedGroup;
}

function docsScreenshotCategory(CategoryGroup $group, string $title, string $slug): Category
{
    $site = docsScreenshotSite();
    $existing = Category::find()
        ->groupId($group->id)
        ->siteId($site->id)
        ->slug($slug)
        ->one();

    if ($existing) {
        return $existing;
    }

    $category = new Category([
        'groupId' => $group->id,
        'siteId' => $site->id,
        'title' => $title,
        'slug' => $slug,
        'enabled' => true,
    ]);

    if (!Craft::$app->getElements()->saveElement($category)) {
        throw new RuntimeException("Unable to save category `{$title}`: " . json_encode($category->getErrors()));
    }

    return $category;
}

function docsScreenshotVolume(): Volume
{
    $fsService = Craft::$app->getFs();
    $fs = $fsService->getFilesystemByHandle(DOCS_SCREENSHOT_FS_HANDLE);

    if (!$fs) {
        $fs = new Local([
            'name' => 'Docs Screenshot Assets',
            'handle' => DOCS_SCREENSHOT_FS_HANDLE,
            'hasUrls' => true,
            'url' => '@web/docs-screenshot-assets',
            'path' => '@webroot/docs-screenshot-assets',
        ]);

        if (!$fsService->saveFilesystem($fs)) {
            throw new RuntimeException('Unable to save docs screenshot filesystem: ' . json_encode($fs->getErrors()));
        }
    }

    $volumesService = Craft::$app->getVolumes();
    $volume = $volumesService->getVolumeByHandle(DOCS_SCREENSHOT_VOLUME_HANDLE);

    if ($volume) {
        return $volume;
    }

    $volume = new Volume([
        'name' => 'Docs Screenshot Assets',
        'handle' => DOCS_SCREENSHOT_VOLUME_HANDLE,
        'fsHandle' => DOCS_SCREENSHOT_FS_HANDLE,
        'transformFsHandle' => DOCS_SCREENSHOT_FS_HANDLE,
    ]);

    if (!$volumesService->saveVolume($volume)) {
        throw new RuntimeException('Unable to save docs screenshot volume: ' . json_encode($volume->getErrors()));
    }

    $savedVolume = $volumesService->getVolumeByHandle(DOCS_SCREENSHOT_VOLUME_HANDLE);

    if (!$savedVolume) {
        throw new RuntimeException('Docs screenshot volume could not be reloaded.');
    }

    return $savedVolume;
}

function docsScreenshotAsset(string $title, string $filename): Asset
{
    $volume = docsScreenshotVolume();
    $existing = Asset::find()
        ->volumeId($volume->id)
        ->filename($filename)
        ->status(null)
        ->one();

    if ($existing) {
        return $existing;
    }

    $folder = Craft::$app->getAssets()->getRootFolderByVolumeId($volume->id);

    if (!$folder) {
        throw new RuntimeException('Docs screenshot asset root folder is missing.');
    }

    $tempPath = Craft::$app->getPath()->getTempPath() . '/' . uniqid('docs-screenshot-asset-', true) . '.png';
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);

    if ($png === false || file_put_contents($tempPath, $png) === false) {
        throw new RuntimeException('Unable to write docs screenshot asset temp file.');
    }

    $volumeFs = $volume->getFs();
    $existingFilePath = rtrim($volumeFs->getRootPath(), '/') . '/' . $filename;

    if (is_file($existingFilePath)) {
        unlink($existingFilePath);
    }

    $asset = new Asset();
    $asset->tempFilePath = $tempPath;
    $asset->filename = $filename;
    $asset->newFolderId = $folder->id;
    $asset->volumeId = $volume->id;
    $asset->title = $title;
    $asset->enabled = true;

    if (!Craft::$app->getElements()->saveElement($asset)) {
        $existing = Asset::find()
            ->volumeId($volume->id)
            ->filename($filename)
            ->status(null)
            ->one();

        if ($existing) {
            return $existing;
        }

        throw new RuntimeException("Unable to save asset `{$title}`: " . json_encode($asset->getErrors()));
    }

    return $asset;
}

function docsScreenshotCustomNode(MenuSettings $menu, string $title, string $url, ?Node $parent = null, bool $enabled = true): Node
{
    $node = new Node([
        'menuId' => $menu->id,
        'siteId' => docsScreenshotSite()->id,
        'type' => Custom::class,
        'title' => $title,
        'url' => $url,
        'enabled' => $enabled,
    ]);

    if ($parent) {
        $node->setParentId($parent->id);
    }

    if (!Craft::$app->getElements()->saveElement($node)) {
        throw new RuntimeException("Unable to save custom node `{$title}`: " . json_encode($node->getErrors()));
    }

    return $node;
}

function docsScreenshotElementNode(
    MenuSettings $menu,
    Entry|Category|Asset $element,
    ?Node $parent = null,
    bool $enabled = true,
    ?string $title = null,
): Node {
    $typeClass = NodeTypeHelper::resolveTypeClass($element::class) ?? $element::class;
    $title ??= ($element->title ?? null) ?: sprintf('%s %s', $element::displayName(), $element->id);

    $node = new Node([
        'menuId' => $menu->id,
        'siteId' => docsScreenshotSite()->id,
        'type' => $typeClass,
        'elementId' => $element->id,
        'title' => $title,
        'enabled' => $enabled,
    ]);

    $node->setLinkedElementSiteId($element->siteId);

    if ($parent) {
        $node->setParentId($parent->id);
    }

    if (!Craft::$app->getElements()->saveElement($node)) {
        throw new RuntimeException("Unable to save element node `{$element->title}`: " . json_encode($node->getErrors()));
    }

    return $node;
}

$menu = docsScreenshotMenu();
docsScreenshotClearMenuNodes($menu);

$ginSection = docsScreenshotSection(DOCS_SCREENSHOT_SECTION_HANDLE, 'Gin');
$ginEntry = docsScreenshotEntry($ginSection, 'Gin', 'gin');
$hendricksEntry = docsScreenshotEntry($ginSection, "Hendrick's Gin", 'hendricks-gin');
$bombayEntry = docsScreenshotEntry($ginSection, 'Bombay Sapphire', 'bombay-sapphire');
$nikkaEntry = docsScreenshotEntry($ginSection, 'Nikka Coffey', 'nikka-coffey');

$ginNode = docsScreenshotElementNode($menu, $ginEntry, null, true, 'Gin');
docsScreenshotElementNode($menu, $hendricksEntry, $ginNode, true, "Hendrick's Gin");
docsScreenshotElementNode($menu, $bombayEntry, $ginNode, true, 'Bombay Sapphire');
docsScreenshotCustomNode($menu, 'Forty Spotted', 'https://fortyspotted.com', $ginNode);
docsScreenshotElementNode($menu, $nikkaEntry, $ginNode, true, 'Nikka Coffey');

$spiritsGroup = docsScreenshotCategoryGroup(DOCS_SCREENSHOT_CATEGORY_GROUP_HANDLE, 'Spirits');
$vodkaCategory = docsScreenshotCategory($spiritsGroup, 'Vodka', 'vodka');
$absolutCategory = docsScreenshotCategory($spiritsGroup, 'Absolut', 'absolut');
$belvedereCategory = docsScreenshotCategory($spiritsGroup, 'Belvedere', 'belvedere');
$svedkaCategory = docsScreenshotCategory($spiritsGroup, 'Svedka', 'svedka');
$cirocCategory = docsScreenshotCategory($spiritsGroup, 'Cîroc', 'ciroc');

$vodkaNode = docsScreenshotElementNode($menu, $vodkaCategory, null, true, 'Vodka');
docsScreenshotElementNode($menu, $absolutCategory, $vodkaNode, true, 'Absolut');
docsScreenshotElementNode($menu, $belvedereCategory, $vodkaNode, true, 'Belvedere');
docsScreenshotElementNode($menu, $svedkaCategory, $vodkaNode, false, 'Svedka');
docsScreenshotElementNode($menu, $cirocCategory, $vodkaNode, true, 'Cîroc');

$whiskyNode = docsScreenshotCustomNode($menu, 'Whisky', '/whisky');

foreach ([
    ['Laphroaig', 'laphroaig.png'],
    ['Glenfiddich', 'glenfiddich.png'],
    ['The Macallan', 'the-macallan.png'],
    ['Ardbeg', 'ardbeg.png'],
] as [$assetTitle, $filename]) {
    $asset = docsScreenshotAsset($assetTitle, $filename);
    docsScreenshotElementNode($menu, $asset, $whiskyNode, true, $assetTitle);
}

echo (int) $menu->id;
