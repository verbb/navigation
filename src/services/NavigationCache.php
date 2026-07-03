<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\deprecations\NavigationCacheDeprecations;
use verbb\navigation\elements\db\NodeQuery;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\events\NavigationCacheInvalidationEvent;
use verbb\navigation\models\Settings;
use verbb\navigation\nodetypes\Dynamic;

use Craft;
use craft\base\Component;
use craft\helpers\Json;

use yii\caching\TagDependency;

class NavigationCache extends Component
{
    // Constants
    // =========================================================================

    public const MODE_OFF = 'off';
    public const MODE_AUTO = 'auto';
    public const MODE_STATIC = 'static';
    public const MODE_MANUAL = 'manual';

    public const PROFILE_LITE = 'lite';
    public const PROFILE_STANDARD = 'standard';

    public const EVENT_INVALIDATE = 'invalidateNavigationCache';
    public const PROFILE_FULL = 'full';

    /** @deprecated in 4.0.0. Legacy cache tag prefix from before the menu rename. */
    private const LEGACY_MENU_TAG_PREFIX = 'navigation:nav:';


    // Traits
    // =========================================================================

    use NavigationCacheDeprecations;


    // Public Methods
    // =========================================================================

    public function isEnabled(): bool
    {
        $mode = $this->_getMode();

        return $mode !== self::MODE_OFF;
    }

    public function shouldCacheQuery(NodeQuery $query): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if ($query->bypassReadCache || $query->internalReadFetch || $query->withLinkedElements || $query->withMenu) {
            return false;
        }

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return false;
        }

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return false;
        }

        if (!$query->menuId && !$query->handle) {
            return false;
        }

        if ($this->_getMode() === self::MODE_MANUAL && !$query->useNavigationCache) {
            return false;
        }

        return true;
    }

    public function getCachedNodes(NodeQuery $query): ?array
    {
        if (!$this->shouldCacheQuery($query)) {
            return null;
        }

        $payload = Craft::$app->getCache()->get($this->buildCacheKey($query));

        if (!is_array($payload) || !isset($payload['nodes'])) {
            return null;
        }

        return $this->hydrateNodes($payload);
    }

    public function setCachedNodes(NodeQuery $query, array $nodes): void
    {
        if (!$this->isEnabled() || !$this->_isCacheableScope($query)) {
            return;
        }

        $payload = $this->exportNodes($nodes, $this->_getProfile());
        $tags = array_merge(
            $this->buildTagsForQuery($query),
            $this->collectSectionTagsFromNodes($nodes),
        );

        Craft::$app->getCache()->set(
            $this->buildCacheKey($query),
            $payload,
            $this->_getDuration(),
            new TagDependency(['tags' => $tags]),
        );
    }

    public function buildCacheKey(NodeQuery $query): string
    {
        $menuUid = $this->_resolveMenuUid($query);
        $siteId = $this->_resolveSiteId($query);
        $profile = $this->_getProfile();

        return sprintf(
            'navigation:tree:%s:%s:%s:%s',
            $menuUid ?? 'unknown',
            $siteId ?? 'all',
            $profile,
            $this->buildCriteriaHash($query),
        );
    }

    public function buildCriteriaHash(NodeQuery $query): string
    {
        $criteria = [
            'status' => $query->status ?? null,
            'type' => $query->type,
            'level' => $query->level,
            'id' => $query->id,
            'limit' => $query->limit,
            'offset' => $query->offset ?? 0,
            'enabled' => $query->enabled,
            'hasUrl' => $query->hasUrl,
            'withNodeHierarchy' => $query->withNodeHierarchy,
            'withProjectedChildren' => $query->withProjectedChildren,
        ];

        return sha1(Json::encode($criteria));
    }

    public function buildTagsForQuery(NodeQuery $query): array
    {
        $tags = [];
        $menuUid = $this->_resolveMenuUid($query);
        $siteId = $this->_resolveSiteId($query);

        if ($menuUid) {
            $tags[] = $this->menuTag($menuUid);

            if ($siteId) {
                $tags[] = $this->menuSiteTag($menuUid, $siteId);
            }
        }

        return $tags;
    }

    public function menuTag(string $menuUid): string
    {
        return 'navigation:menu:' . $menuUid;
    }

    public function menuSiteTag(string $menuUid, int $siteId): string
    {
        return 'navigation:menu:' . $menuUid . ':site:' . $siteId;
    }

    public function nodeTag(int $nodeId): string
    {
        return 'navigation:node:' . $nodeId;
    }

    public function sectionTag(string $sectionUid): string
    {
        return 'navigation:section:' . $sectionUid;
    }

    public function categoryGroupTag(string $groupUid): string
    {
        return 'navigation:categoryGroup:' . $groupUid;
    }

    public function volumeTag(string $volumeUid): string
    {
        return 'navigation:volume:' . $volumeUid;
    }

    public function productTypeTag(string $productTypeUid): string
    {
        return 'navigation:productType:' . $productTypeUid;
    }

    public function collectSectionTagsFromNodes(array $nodes): array
    {
        $tags = [];

        foreach ($nodes as $node) {
            if (!$node instanceof NodeElement) {
                continue;
            }

            if ($node->type !== Dynamic::class) {
                continue;
            }

            $tags = array_merge($tags, Navigation::$plugin->getDynamicSources()->getCacheTagsForNode($node));
        }

        return array_values(array_unique($tags));
    }

    public function invalidateSection(string $sectionUid): void
    {
        $this->invalidateTags([$this->sectionTag($sectionUid)]);
    }

    public function invalidateCategoryGroup(string $groupUid): void
    {
        $this->invalidateTags([$this->categoryGroupTag($groupUid)]);
    }

    public function invalidateVolume(string $volumeUid): void
    {
        $this->invalidateTags([$this->volumeTag($volumeUid)]);
    }

    public function invalidateProductType(string $productTypeUid): void
    {
        $this->invalidateTags([$this->productTypeTag($productTypeUid)]);
    }

    public function invalidateTags(array $tags): void
    {
        TagDependency::invalidate(Craft::$app->getCache(), $tags);
        $this->_triggerInvalidateEvent($tags);
    }

    public function invalidateMenu(string $menuUid): void
    {
        $tags = $this->_menuInvalidationTags($menuUid);
        TagDependency::invalidate(Craft::$app->getCache(), $tags);
        $this->_triggerInvalidateEvent($tags, menuUid: $menuUid);
    }

    public function invalidateMenuSite(string $menuUid, int $siteId): void
    {
        $tags = array_merge(
            $this->_menuInvalidationTags($menuUid),
            [$this->menuSiteTag($menuUid, $siteId)],
        );
        TagDependency::invalidate(Craft::$app->getCache(), $tags);
        $this->_triggerInvalidateEvent($tags, menuUid: $menuUid, siteId: $siteId);
    }

    public function invalidateNode(int $nodeId, ?string $menuUid = null, ?int $siteId = null): void
    {
        $tags = [$this->nodeTag($nodeId)];

        if ($menuUid) {
            $tags = array_merge($tags, $this->_menuInvalidationTags($menuUid));

            if ($siteId) {
                $tags[] = $this->menuSiteTag($menuUid, $siteId);
            }
        }

        TagDependency::invalidate(Craft::$app->getCache(), $tags);
        $this->_triggerInvalidateEvent($tags, menuUid: $menuUid, siteId: $siteId, nodeId: $nodeId);
    }

    public function invalidateByHandle(?string $handle = null): void
    {
        if ($handle === null) {
            $tags = ['navigation'];
            TagDependency::invalidate(Craft::$app->getCache(), $tags);
            $this->_triggerInvalidateEvent($tags);

            return;
        }

        $menu = Navigation::$plugin->getMenus()->getMenuByHandle($handle);

        if ($menu) {
            $this->invalidateMenu($menu->uid);
        }
    }

    public function exportNodes(array $nodes, string $profile): array
    {
        $exported = [];

        foreach ($nodes as $node) {
            if (!$node instanceof NodeElement) {
                continue;
            }

            $exported[] = $this->exportNode($node, $profile);
        }

        return ['nodes' => $exported, 'profile' => $profile];
    }

    public function exportNode(NodeElement $node, string $profile): array
    {
        $data = [
            'id' => $node->id,
            'title' => $node->title,
            'level' => $node->level,
            'menuId' => $node->menuId,
            'siteId' => $node->siteId,
            'type' => $node->type,
            'url' => $node->getRawUrl(),
            'classes' => $node->classes,
            'newWindow' => $node->newWindow,
            'customAttributes' => $node->customAttributes,
            'urlSuffix' => $node->urlSuffix,
            'data' => $node->data,
            'elementId' => $node->elementId,
            'elementUrl' => $node->getRawElementUrl(),
            'enabled' => $node->enabled,
            'enabledForSite' => $node->getEnabledForSite(),
        ];

        if ($profile === self::PROFILE_STANDARD || $profile === self::PROFILE_FULL) {
            $data['fieldValues'] = $node->getSerializedFieldValues();
        }

        if ($profile === self::PROFILE_FULL && $node->getElement()) {
            $data['elementSnapshot'] = $node->getElement()->toArray();
        }

        return $data;
    }

    public function hydrateNodes(array $payload): array
    {
        $nodes = [];

        foreach ($payload['nodes'] as $nodeData) {
            $node = new NodeElement();
            $node->id = $nodeData['id'];
            $node->title = $nodeData['title'];
            $node->level = $nodeData['level'];
            $node->menuId = $nodeData['menuId'];
            $node->siteId = $nodeData['siteId'];
            $node->type = $nodeData['type'];
            $node->newWindow = (bool)($nodeData['newWindow'] ?? false);
            $node->classes = $nodeData['classes'] ?? null;
            $node->urlSuffix = $nodeData['urlSuffix'] ?? null;
            $node->customAttributes = $nodeData['customAttributes'] ?? [];
            $node->data = $nodeData['data'] ?? [];
            $node->elementId = $nodeData['elementId'] ?? null;
            $node->enabled = (bool)($nodeData['enabled'] ?? true);
            $node->setEnabledForSite((bool)($nodeData['enabledForSite'] ?? true));
            $node->setUrl($nodeData['url'] ?? null);
            $node->setElementUrl($nodeData['elementUrl'] ?? null);

            if (!empty($nodeData['fieldValues'])) {
                $node->setFieldValues($nodeData['fieldValues']);
            }

            if (!empty($nodeData['elementSnapshot'])) {
                // Full profile snapshots are re-applied lazily when templates access node.element.
            }

            $nodes[] = $node;
        }

        return $nodes;
    }


    // Private Methods
    // =========================================================================

    private function _menuInvalidationTags(string $menuUid): array
    {
        return [
            $this->menuTag($menuUid),
            self::LEGACY_MENU_TAG_PREFIX . $menuUid,
        ];
    }

    private function _getMode(): string
    {
        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();

        return $settings->cacheMode ?? self::MODE_AUTO;
    }

    private function _getProfile(): string
    {
        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();

        return $settings->cacheProfile ?? self::PROFILE_STANDARD;
    }

    private function _getDuration(): ?int
    {
        if ($this->_getMode() === self::MODE_STATIC) {
            /* @var Settings $settings */
            $settings = Navigation::$plugin->getSettings();

            return $settings->cacheDuration ?? 86400;
        }

        return null;
    }

    private function _resolveMenuUid(NodeQuery $query): ?string
    {
        if ($query->handle) {
            $menu = Navigation::$plugin->getMenus()->getMenuByHandle($query->handle);

            return $menu?->uid;
        }

        if ($query->menuId) {
            $menuId = is_array($query->menuId) ? reset($query->menuId) : $query->menuId;
            $menu = Navigation::$plugin->getMenus()->getMenuById((int)$menuId);

            return $menu?->uid;
        }

        return null;
    }

    private function _resolveSiteId(NodeQuery $query): ?int
    {
        if ($query->siteId === null || $query->siteId === '*') {
            return Craft::$app->getSites()->getCurrentSite()->id;
        }

        if (is_array($query->siteId)) {
            return (int)reset($query->siteId);
        }

        return (int)$query->siteId;
    }

    private function _isCacheableScope(NodeQuery $query): bool
    {
        return (bool)($query->menuId || $query->handle);
    }

    private function _triggerInvalidateEvent(array $tags, ?string $menuUid = null, ?int $siteId = null, ?int $nodeId = null): void
    {
        if (!$this->hasEventHandlers(self::EVENT_INVALIDATE)) {
            return;
        }

        $this->trigger(self::EVENT_INVALIDATE, new NavigationCacheInvalidationEvent([
            'tags' => $tags,
            'menuUid' => $menuUid,
            'siteId' => $siteId,
            'nodeId' => $nodeId,
        ]));
    }
}
