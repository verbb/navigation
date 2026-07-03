<?php

declare(strict_types=1);

namespace Tests\Support\Fixtures;

use Craft;
use craft\base\ElementInterface;
use craft\elements\Category;
use craft\elements\Entry;
use craft\fieldlayoutelements\CustomField;
use craft\fields\PlainText;
use craft\helpers\StringHelper;
use craft\models\CategoryGroup;
use craft\models\CategoryGroup_SiteSettings;
use craft\models\EntryType;
use craft\models\FieldLayoutTab;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use craft\models\Site;
use verbb\navigation\elements\Node;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\models\MenuSiteSettings;
use verbb\navigation\Navigation;
use verbb\navigation\elements\Menu;
use verbb\navigation\helpers\NodeTypeHelper;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\GroupColumn;
use verbb\navigation\nodetypes\Passive;
use verbb\navigation\nodetypes\Site as SiteNodeType;
use verbb\navigation\dynamic\sources\CategoryGroupDynamicSource;
use verbb\navigation\dynamic\sources\EntrySectionDynamicSource;
use verbb\navigation\nodetypes\Dynamic;
use DateTime;
use RuntimeException;

class NavigationFixtureFactory
{
    private static int $sequence = 0;

    public static function menu(?string $handle = null, string $propagationMethod = MenuSettings::PROPAGATION_METHOD_ALL): MenuSettings
    {
        $handle ??= self::handle('mainNavigation');

        $menu = new MenuSettings([
            'name' => StringHelper::titleize($handle),
            'handle' => $handle,
            'instructions' => '',
            'propagationMethod' => $propagationMethod,
            'sortOrder' => 1,
            'uid' => StringHelper::UUID(),
        ]);

        $menu->setSiteSettings(array_map(
            static fn(Site $site): MenuSiteSettings => new MenuSiteSettings([
                'siteId' => $site->id,
                'enabled' => true,
            ]),
            Craft::$app->getSites()->getAllSites(),
        ));

        if (!Navigation::$plugin->getMenus()->saveMenu($menu)) {
            throw new RuntimeException('Failed creating navigation fixture: ' . json_encode($menu->getErrors()));
        }

        $savedMenu = Navigation::$plugin->getMenus()->getMenuByHandle($handle);

        if (!$savedMenu) {
            throw new RuntimeException("Navigation fixture `{$handle}` could not be reloaded.");
        }

        return $savedMenu;
    }

    /** @deprecated Use {@see menu()} instead. */
    public static function nav(?string $handle = null, string $propagationMethod = MenuSettings::PROPAGATION_METHOD_ALL): MenuSettings
    {
        return self::menu($handle, $propagationMethod);
    }

    public static function customNode(MenuSettings $menu, string $title, string $url, ?Node $parent = null, ?int $siteId = null): Node
    {
        $siteId ??= Craft::$app->getSites()->getPrimarySite()->id;
        $node = new Node([
            'menuId' => $menu->id,
            'siteId' => $siteId,
            'type' => Custom::class,
            'title' => $title,
            'url' => $url,
            'enabled' => true,
        ]);

        if ($parent) {
            $node->setParentId($parent->id);
        }

        if (!Craft::$app->getElements()->saveElement($node)) {
            throw new RuntimeException('Failed creating node fixture: ' . json_encode($node->getErrors()));
        }

        return $node;
    }

    public static function dynamicCategoryGroupNode(MenuSettings $menu, CategoryGroup $group, ?Node $parent = null, array $data = []): Node
    {
        $node = new Node([
            'menuId' => $menu->id,
            'siteId' => Craft::$app->getSites()->getPrimarySite()->id,
            'type' => Dynamic::class,
            'title' => $group->name,
            'data' => array_merge([
                'dynamicSource' => CategoryGroupDynamicSource::handle(),
                'groupId' => (string)$group->id,
            ], $data),
            'enabled' => true,
        ]);

        if ($parent) {
            $node->setParentId($parent->id);
        }

        if (!Craft::$app->getElements()->saveElement($node)) {
            throw new RuntimeException('Failed creating dynamic category group node fixture: ' . json_encode($node->getErrors()));
        }

        return $node;
    }

    public static function groupColumnNode(MenuSettings $menu, string $title, ?Node $parent = null): Node
    {
        $node = new Node([
            'menuId' => $menu->id,
            'siteId' => Craft::$app->getSites()->getPrimarySite()->id,
            'type' => GroupColumn::class,
            'title' => $title,
            'enabled' => true,
        ]);

        if ($parent) {
            $node->setParentId($parent->id);
        }

        if (!Craft::$app->getElements()->saveElement($node)) {
            throw new RuntimeException('Failed creating group column node fixture: ' . json_encode($node->getErrors()));
        }

        return $node;
    }

    public static function passiveNode(MenuSettings $menu, string $title, ?Node $parent = null): Node
    {
        $node = new Node([
            'menuId' => $menu->id,
            'siteId' => Craft::$app->getSites()->getPrimarySite()->id,
            'type' => Passive::class,
            'title' => $title,
            'enabled' => true,
        ]);

        if ($parent) {
            $node->setParentId($parent->id);
        }

        if (!Craft::$app->getElements()->saveElement($node)) {
            throw new RuntimeException('Failed creating passive node fixture: ' . json_encode($node->getErrors()));
        }

        return $node;
    }

    public static function siteNode(MenuSettings $menu, Site $site, ?Node $parent = null): Node
    {
        $node = new Node([
            'menuId' => $menu->id,
            'siteId' => Craft::$app->getSites()->getPrimarySite()->id,
            'type' => SiteNodeType::class,
            'title' => $site->name,
            'data' => ['siteId' => $site->id],
            'enabled' => true,
        ]);

        if ($parent) {
            $node->setParentId($parent->id);
        }

        if (!Craft::$app->getElements()->saveElement($node)) {
            throw new RuntimeException('Failed creating site node fixture: ' . json_encode($node->getErrors()));
        }

        return $node;
    }

    public static function entrySection(?string $handle = null): Section
    {
        $handle ??= self::handle('navigationTestEntries');
        $entryTypeHandle = $handle . 'Type';
        $entries = Craft::$app->getEntries();

        $entryType = new EntryType([
            'name' => StringHelper::titleize($entryTypeHandle),
            'handle' => $entryTypeHandle,
            'hasTitleField' => true,
        ]);

        if (!$entries->saveEntryType($entryType)) {
            throw new RuntimeException('Failed creating entry type fixture: ' . json_encode($entryType->getErrors()));
        }

        $section = new Section([
            'name' => StringHelper::titleize($handle),
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
                'template' => '_navigation-test/entry',
            ]),
            Craft::$app->getSites()->getAllSites(),
        ));

        if (!$entries->saveSection($section)) {
            throw new RuntimeException('Failed creating section fixture: ' . json_encode($section->getErrors()));
        }

        $savedSection = $entries->getSectionByHandle($handle);

        if (!$savedSection) {
            throw new RuntimeException("Section fixture `{$handle}` could not be reloaded.");
        }

        return $savedSection;
    }

    /**
     * @return Entry[]
     */
    public static function entries(int $count, ?Section $section = null): array
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $section ??= self::entrySection();
        $entryType = Craft::$app->getEntries()->getEntryTypesBySectionId($section->id)[0] ?? null;

        if (!$entryType) {
            throw new RuntimeException("Section `{$section->handle}` has no entry types.");
        }

        $entries = [];

        for ($i = 1; $i <= $count; $i++) {
            $slug = sprintf('%s-entry-%02d', $section->handle, $i);
            $entry = new Entry([
                'sectionId' => $section->id,
                'typeId' => $entryType->id,
                'siteId' => $site->id,
                'title' => sprintf('Navigation Test Entry %02d', $i),
                'slug' => $slug,
                'postDate' => new DateTime(),
                'enabled' => true,
            ]);

            if (!Craft::$app->getElements()->saveElement($entry)) {
                throw new RuntimeException('Failed creating entry fixture: ' . json_encode($entry->getErrors()));
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    public static function entryNode(MenuSettings $menu, Entry $entry, ?Node $parent = null): Node
    {
        return self::elementNode($menu, $entry, $parent);
    }

    public static function dynamicSectionNode(MenuSettings $menu, Section $section, ?Node $parent = null, array $data = []): Node
    {
        $node = new Node([
            'menuId' => $menu->id,
            'siteId' => Craft::$app->getSites()->getPrimarySite()->id,
            'type' => Dynamic::class,
            'title' => $section->name,
            'data' => array_merge([
                'dynamicSource' => EntrySectionDynamicSource::handle(),
                'sectionId' => (string)$section->id,
            ], $data),
            'enabled' => true,
        ]);

        if ($parent) {
            $node->setParentId($parent->id);
        }

        if (!Craft::$app->getElements()->saveElement($node)) {
            throw new RuntimeException('Failed creating dynamic section node fixture: ' . json_encode($node->getErrors()));
        }

        return $node;
    }

    public static function homepageEntry(?string $handle = null): Entry
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $entry = Entry::find()
            ->siteId($site->id)
            ->uri('__home__')
            ->one();

        if ($entry) {
            return $entry;
        }

        $handle ??= self::handle('navigationHome');
        $entriesService = Craft::$app->getEntries();
        $entryTypeHandle = $handle . 'Type';

        $entryType = new EntryType([
            'name' => StringHelper::titleize($entryTypeHandle),
            'handle' => $entryTypeHandle,
            'hasTitleField' => true,
        ]);

        if (!$entriesService->saveEntryType($entryType)) {
            throw new RuntimeException('Failed creating homepage entry type fixture: ' . json_encode($entryType->getErrors()));
        }

        $section = new Section([
            'name' => StringHelper::titleize($handle),
            'handle' => $handle,
            'type' => Section::TYPE_SINGLE,
        ]);
        $section->setEntryTypes([$entryType]);
        $section->setSiteSettings([
            new Section_SiteSettings([
                'siteId' => $site->id,
                'enabledByDefault' => true,
                'hasUrls' => true,
                'uriFormat' => '__home__',
                'template' => '_navigation-test/home',
            ]),
        ]);

        if (!$entriesService->saveSection($section)) {
            throw new RuntimeException('Failed creating homepage section fixture: ' . json_encode($section->getErrors()));
        }

        $section = $entriesService->getSectionByHandle($handle);
        $entry = Entry::find()->sectionId($section->id)->siteId($site->id)->one();

        if (!$entry) {
            $entry = new Entry([
                'sectionId' => $section->id,
                'typeId' => $entryType->id,
                'siteId' => $site->id,
                'title' => 'Homepage',
                'slug' => $handle,
                'postDate' => new DateTime(),
                'enabled' => true,
            ]);

            if (!Craft::$app->getElements()->saveElement($entry)) {
                throw new RuntimeException('Failed creating homepage entry fixture: ' . json_encode($entry->getErrors()));
            }
        }

        return $entry;
    }

    public static function categoryGroup(?string $handle = null): CategoryGroup
    {
        $handle ??= self::handle('navigationTestCategories');
        $group = new CategoryGroup([
            'name' => StringHelper::titleize($handle),
            'handle' => $handle,
            'maxLevels' => null,
        ]);
        $group->setSiteSettings(array_map(
            static fn(Site $site): CategoryGroup_SiteSettings => new CategoryGroup_SiteSettings([
                'siteId' => $site->id,
                'hasUrls' => true,
                'uriFormat' => $handle . '/{slug}',
                'template' => '_navigation-test/category',
            ]),
            Craft::$app->getSites()->getAllSites(),
        ));

        if (!Craft::$app->getCategories()->saveGroup($group)) {
            throw new RuntimeException('Failed creating category group fixture: ' . json_encode($group->getErrors()));
        }

        $savedGroup = Craft::$app->getCategories()->getGroupByHandle($handle);

        if (!$savedGroup) {
            throw new RuntimeException("Category group fixture `{$handle}` could not be reloaded.");
        }

        return $savedGroup;
    }

    /**
     * @return Category[]
     */
    public static function categories(int $count, ?CategoryGroup $group = null): array
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $group ??= self::categoryGroup();
        $categories = [];

        for ($i = 1; $i <= $count; $i++) {
            $category = new Category([
                'groupId' => $group->id,
                'siteId' => $site->id,
                'title' => sprintf('Navigation Test Category %02d', $i),
                'slug' => sprintf('%s-category-%02d', $group->handle, $i),
                'enabled' => true,
            ]);

            if (!Craft::$app->getElements()->saveElement($category)) {
                throw new RuntimeException('Failed creating category fixture: ' . json_encode($category->getErrors()));
            }

            $categories[] = $category;
        }

        return $categories;
    }

    public static function categoryNode(MenuSettings $menu, Category $category, ?Node $parent = null): Node
    {
        return self::elementNode($menu, $category, $parent);
    }

    public static function elementNode(MenuSettings $menu, ElementInterface $element, ?Node $parent = null): Node
    {
        $typeClass = NodeTypeHelper::resolveTypeClass(get_class($element)) ?? get_class($element);

        $node = new Node([
            'menuId' => $menu->id,
            'siteId' => Craft::$app->getSites()->getPrimarySite()->id,
            'type' => $typeClass,
            'elementId' => $element->id,
            'title' => ($element->title ?? null) ?: sprintf('%s %s', $element::displayName(), $element->id),
            'enabled' => true,
        ]);

        $node->setLinkedElementSiteId($element->siteId);

        if ($parent) {
            $node->setParentId($parent->id);
        }

        if (!Craft::$app->getElements()->saveElement($node)) {
            throw new RuntimeException('Failed creating element-linked node fixture: ' . json_encode($node->getErrors()));
        }

        return $node;
    }

    /**
     * @return Node[]
     */
    public static function flatCustomNodes(MenuSettings $menu, int $count): array
    {
        $nodes = [];

        for ($i = 1; $i <= $count; $i++) {
            $nodes[] = self::customNode($menu, "Flat {$i}", "/flat-{$i}");
        }

        return $nodes;
    }

    /**
     * @return Node[]
     */
    public static function deepCustomNodes(MenuSettings $menu, int $depth): array
    {
        $nodes = [];
        $parent = null;

        for ($i = 1; $i <= $depth; $i++) {
            $parent = self::customNode($menu, "Level {$i}", '/level-' . implode('/', range(1, $i)), $parent);
            $nodes[] = $parent;
        }

        return $nodes;
    }

    /**
     * Creates a wide tree: multiple root nodes each with the same number of children.
     *
     * @return Node[]
     */
    public static function wideCustomNodes(MenuSettings $menu, int $roots, int $childrenPerRoot): array
    {
        $nodes = [];

        for ($i = 1; $i <= $roots; $i++) {
            $parent = self::customNode($menu, "Root {$i}", "/root-{$i}");
            $nodes[] = $parent;

            for ($j = 1; $j <= $childrenPerRoot; $j++) {
                $nodes[] = self::customNode($menu, "Root {$i} Child {$j}", "/root-{$i}/child-{$j}", $parent);
            }
        }

        return $nodes;
    }

    public static function disabledCustomNode(MenuSettings $menu, string $title, string $url, ?Node $parent = null): Node
    {
        $node = self::customNode($menu, $title, $url, $parent);
        $node->enabled = false;

        if (!Craft::$app->getElements()->saveElement($node)) {
            throw new RuntimeException('Failed disabling node fixture: ' . json_encode($node->getErrors()));
        }

        return $node;
    }

    public static function addPlainTextFieldToMenu(MenuSettings $menu, ?string $handle = null): PlainText
    {
        $handle ??= self::handle('navigationNodeText');
        $field = new PlainText([
            'name' => StringHelper::titleize($handle),
            'handle' => $handle,
        ]);

        if (!Craft::$app->getFields()->saveField($field)) {
            throw new RuntimeException('Failed creating plain text field fixture: ' . json_encode($field->getErrors()));
        }

        $layout = $menu->getFieldLayout();
        $layout->setTabs([
            new FieldLayoutTab([
                'layout' => $layout,
                'name' => 'Content',
                'elements' => [
                    [
                        'type' => CustomField::class,
                        'fieldUid' => $field->uid,
                    ],
                ],
            ]),
        ]);

        if (!Navigation::$plugin->getMenus()->saveMenu($menu)) {
            throw new RuntimeException('Failed saving menu field layout fixture: ' . json_encode($menu->getErrors()));
        }

        return $field;
    }

    /** @deprecated Use {@see addPlainTextFieldToMenu()} instead. */
    public static function addPlainTextFieldToNav(MenuSettings $menu, ?string $handle = null): PlainText
    {
        return self::addPlainTextFieldToMenu($menu, $handle);
    }

    public static function customNodeWithField(MenuSettings $menu, PlainText $field, string $value): Node
    {
        $node = self::customNode($menu, $value, '/' . StringHelper::toKebabCase($value));
        $node->setFieldValue($field->handle, $value);

        if (!Craft::$app->getElements()->saveElement($node)) {
            throw new RuntimeException('Failed saving custom field value fixture: ' . json_encode($node->getErrors()));
        }

        return $node;
    }

    public static function existingSecondarySite(): Site
    {
        $primary = Craft::$app->getSites()->getPrimarySite();

        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            if ($site->id !== $primary->id) {
                return $site;
            }
        }

        if (!Craft::$app->getSites()->getRemainingSites()) {
            throw new RuntimeException('No secondary site available and Craft site limit reached.');
        }

        return self::secondarySite('navigationTestSecondary');
    }

    public static function secondarySite(?string $handle = null): Site
    {
        $handle ??= 'navigationTestSecondary';
        $sites = Craft::$app->getSites();

        if ($site = $sites->getSiteByHandle($handle)) {
            return $site;
        }

        if (!$sites->getRemainingSites()) {
            return self::existingSecondarySite();
        }

        $primary = $sites->getPrimarySite();
        $site = new Site([
            'groupId' => $primary->groupId,
            'handle' => $handle,
            'language' => 'en-US',
            'hasUrls' => true,
        ]);
        $site->setName(StringHelper::titleize($handle));
        $site->setBaseUrl("https://{$handle}.test/");

        if (!$sites->saveSite($site)) {
            throw new RuntimeException('Failed creating site fixture: ' . json_encode($site->getErrors()));
        }

        $savedSite = $sites->getSiteByHandle($handle);

        if (!$savedSite) {
            throw new RuntimeException("Site fixture `{$handle}` could not be reloaded.");
        }

        return $savedSite;
    }

    private static function handle(string $prefix): string
    {
        self::$sequence++;

        return $prefix . self::$sequence . strtolower(StringHelper::randomString(8));
    }
}
