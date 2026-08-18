<?php
namespace verbb\navigation\elements;

use verbb\navigation\Navigation;
use verbb\navigation\base\ElementNodeType;
use verbb\navigation\deprecations\NodeDeprecations;
use verbb\navigation\events\NodeActiveEvent;
use verbb\navigation\elements\conditions\NodeCondition;
use verbb\navigation\elements\db\NodeQuery;
use verbb\navigation\elements\Menu;
use verbb\navigation\elementactions\StageDelete;
use verbb\navigation\elementactions\UnstageDelete;
use verbb\navigation\helpers\NodeTypeHelper;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\models\NodeActiveState;
use verbb\navigation\models\Settings;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\GroupColumn;
use verbb\navigation\nodetypes\Passive;
use verbb\navigation\nodetypes\Site as SiteNodeType;
use verbb\navigation\records\Node as NodeRecord;

use Craft;
use craft\base\Element;
use craft\base\Field;
use craft\base\ElementInterface;
use craft\controllers\ElementIndexesController;
use craft\db\Query;
use craft\db\Table;
use craft\elements\User;
use craft\elements\actions\Delete;
use craft\elements\actions\Duplicate;
use craft\elements\actions\Edit;
use craft\elements\actions\Restore;
use craft\elements\actions\SetStatus;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\db\ElementQuery;
use craft\errors\UnsupportedSiteException;
use craft\events\MoveElementEvent;
use craft\helpers\App;
use craft\helpers\ArrayHelper;
use craft\helpers\Cp;
use craft\helpers\Db;
use craft\helpers\ElementHelper;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use craft\models\Site;
use craft\services\Structures;

use Throwable;

use yii\base\Event;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\BaseHtml;
use yii\validators\Validator;

use Twig\Markup;

class Node extends Element
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('navigation', 'Node');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('navigation', 'Nodes');
    }

    public static function refHandle(): ?string
    {
        return 'node';
    }

    public static function trackChanges(): bool
    {
        return true;
    }

    public static function hasDrafts(): bool
    {
        return true;
    }

    public static function hasTitles(): bool
    {
        return true;
    }

    public static function hasUris(): bool
    {
        return false;
    }

    public static function isLocalized(): bool
    {
        return true;
    }

    public static function hasStatuses(): bool
    {
        return true;
    }

    public static function find(): NodeQuery
    {
        return new NodeQuery(static::class);
    }

    public static function createCondition(): ElementConditionInterface
    {
        return Craft::createObject(NodeCondition::class, [static::class]);
    }

    public static function gqlTypeNameByContext(mixed $context): string
    {
        return $context->handle . '_Node';
    }

    public static function gqlScopesByContext(mixed $context): array
    {
        return ['navigationMenus.' . $context->uid];
    }

    public static function statuses(): array
    {
        return array_merge(parent::statuses(), [
            self::STATUS_PENDING_ADD => Craft::t('navigation', 'Pending'),
            self::STATUS_PENDING_DELETE => Craft::t('navigation', 'Pending deletion'),
            self::STATUS_PENDING_EDIT => Craft::t('navigation', 'Pending edit'),
        ]);
    }

    protected static function defineSources(string $context): array
    {
        $sources = [];

        $navs = Navigation::$plugin->getMenus()->getEditableMenus();

        foreach ($navs as $nav) {
            // Structure UI (handles, nesting) whenever the user can manage the menu.
            // Deferred vs live persistence is handled in JS (DeferredStructureTableSorter).
            $structureEditable = Craft::$app->getUser()->checkPermission("navigation-manageMenu:$nav->uid");

            $sources[] = [
                'key' => 'menu:' . $nav->uid,
                'label' => Craft::t('site', $nav->name),
                'data' => ['handle' => $nav->handle],
                'criteria' => ['menuId' => $nav->id],
                'structureId' => $nav->structureId,
                'structureEditable' => $structureEditable,
            ];
        }

        return $sources;
    }

    protected static function defineFieldLayouts(?string $source): array
    {
        if ($source === null || $source === '*') {
            $navs = Navigation::$plugin->getMenus()->getEditableMenus();
        } else {
            $navs = [];

            if (preg_match('/^(?:nav|menu):(.+)$/', $source, $matches)) {
                $nav = Navigation::$plugin->getMenus()->getMenuByUid($matches[1]);
                
                if ($nav) {
                    $navs[] = $nav;
                }
            }
        }

        return array_map(fn(MenuSettings $nav) => $nav->getFieldLayout(), $navs);
    }

    protected static function defineSortOptions(): array
    {
        // We must override the sort options, otherwise any in `defineTableAttributes` will be added.
        // We really only want a structure sort option, and disallow users from changing, but we run
        // into issues when viewing trashed nodes, which have no structure. Thus, we need at least another option.
        return [
            'id' => Craft::t('app', 'ID'),
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'typeLabel' => ['label' => Craft::t('app', 'Type')],
        ];
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        // These are static and cannot be customised by users
        return [
            'typeLabel',
        ];
    }

    protected static function defineActions(string $source): array
    {
        // Get the selected site
        $controller = Craft::$app->controller;

        if ($controller instanceof ElementIndexesController) {
            /* @var ElementQuery $elementQuery */
            $elementQuery = $controller->getElementQuery();
        } else {
            $elementQuery = null;
        }

        // Get the group we need to check permissions on
        if (preg_match('/^(?:nav|menu):(\d+)$/', $source, $matches)) {
            $nav = Navigation::$plugin->getMenus()->getMenuById($matches[1]);
        } else if (preg_match('/^(?:nav|menu):(.+)$/', $source, $matches)) {
            $nav = Navigation::$plugin->getMenus()->getMenuByUid($matches[1]);
        }

        // Now figure out what we can do with it
        $actions = [];
        $elementsService = Craft::$app->getElements();

        if ($nav !== null) {
            // Set Status
            $actions[] = SetStatus::class;

            // Edit
            $actions[] = $elementsService->createAction([
                'type' => Edit::class,
                'label' => Craft::t('app', 'Edit node'),
            ]);

            // Duplicate
            $actions[] = Duplicate::class;

            if ($nav->maxLevels != 1) {
                $actions[] = [
                    'type' => Duplicate::class,
                    'deep' => true,
                ];
            }

            // Delete
            if (self::_useBuilderStaging($source)) {
                $actions[] = StageDelete::class;

                if ($nav->maxLevels != 1) {
                    $actions[] = [
                        'type' => StageDelete::class,
                        'withDescendants' => true,
                    ];
                }

                $actions[] = UnstageDelete::class;
            } else {
                $actions[] = Delete::class;

                if ($nav->maxLevels != 1) {
                    $actions[] = [
                        'type' => Delete::class,
                        'withDescendants' => true,
                    ];
                }
            }
        }

        // Restore
        $actions[] = $elementsService->createAction([
            'type' => Restore::class,
            'successMessage' => Craft::t('app', 'Nodes restored.'),
            'partialSuccessMessage' => Craft::t('app', 'Some nodes restored.'),
            'failMessage' => Craft::t('app', 'Nodes not restored.'),
        ]);

        return $actions;
    }

    private static function _useBuilderStaging(?string $source): bool
    {
        if (!Navigation::$plugin->getBuildSessions()->isStagingEnabled()) {
            return false;
        }

        if (!Craft::$app->getRequest()->getIsCpRequest()) {
            return false;
        }

        // Menu builder index always uses a menu:/nav: source key.
        return $source !== null && preg_match('/^(?:nav|menu):/', $source);
    }


    // Constants
    // =========================================================================

    public const EVENT_NODE_ACTIVE = 'modifyNodeActive';

    /** Internal builder flag — node was bulk-added and awaits Publish menu. */
    public const PENDING_PUBLISH_DATA_KEY = '_pendingPublish';

    /** Internal builder flag — node is staged for deletion until Publish menu. */
    public const PENDING_DELETE_DATA_KEY = '_pendingDelete';

    /** Stored enabled state to restore when unstaging or recovering orphaned pending deletes. */
    public const PENDING_DELETE_STATE_DATA_KEY = '_pendingDeleteState';

    /** Internal builder flag — node has staged slide-out edits (4.2). */
    public const PENDING_EDIT_DATA_KEY = '_pendingEdit';

    /** Node was auto-disabled because its linked Craft element was soft-deleted. */
    public const LINKED_ELEMENT_DISABLED_DATA_KEY = '_linkedElementDisabled';

    /** Stored enabled state to restore when the linked element is restored. */
    public const LINKED_ELEMENT_DISABLED_STATE_DATA_KEY = '_linkedElementDisabledState';

    public const ENABLED_FOR_PROPAGATED_SITES_DATA_KEY = 'enabledForPropagatedSites';

    public const STATUS_PENDING_ADD = 'pending-add';
    public const STATUS_PENDING_DELETE = 'pending-delete';
    public const STATUS_PENDING_EDIT = 'pending-edit';

    // Traits
    // =========================================================================

    use NodeDeprecations;


    // Properties
    // =========================================================================

    public ?int $id = null;
    public ?int $elementId = null;
    public ?int $siteId = null;
    public ?int $menuId = null;
    public ?string $type = null;
    public ?string $classes = null;
    public ?string $urlSuffix = null;
    public array $customAttributes = [];
    public array $data = [];
    public bool $newWindow = false;
    public ?string $uri = null;
    public ?bool $deletedWithMenu = false;

    private ?string $_url = null;
    private ?ElementInterface $_element = null;
    private array $_nodeTypes = [];
    private ?string $_elementUrl = null;
    private ?NodeActiveState $_activeState = null;
    private bool $_activeStateResolved = false;
    private ?int $_linkedElementSiteId = null;
    private ?Menu $_eagerLoadedMenu = null;
    private bool $_eagerLoadedMenuResolved = false;

    // Public Methods
    // =========================================================================

    public function getIsPendingPublish(): bool
    {
        return !empty($this->data[self::PENDING_PUBLISH_DATA_KEY]);
    }

    public function setPendingPublish(bool $value = true): void
    {
        if ($value) {
            $this->data[self::PENDING_PUBLISH_DATA_KEY] = true;
        } else {
            unset($this->data[self::PENDING_PUBLISH_DATA_KEY]);
        }
    }

    public function clearPendingPublish(): void
    {
        $this->setPendingPublish(false);
    }

    public function getIsPendingDelete(): bool
    {
        return !empty($this->data[self::PENDING_DELETE_DATA_KEY]);
    }

    public function setPendingDelete(bool $value = true): void
    {
        if ($value) {
            $this->data[self::PENDING_DELETE_DATA_KEY] = true;
        } else {
            unset($this->data[self::PENDING_DELETE_DATA_KEY]);
        }
    }

    public function clearPendingDelete(): void
    {
        $this->setPendingDelete(false);
        unset($this->data[self::PENDING_DELETE_STATE_DATA_KEY]);
    }

    public function getPendingDeleteRestoreState(): array
    {
        $state = $this->data[self::PENDING_DELETE_STATE_DATA_KEY] ?? null;

        if (is_array($state)) {
            return [
                'enabled' => (bool)($state['enabled'] ?? true),
                'enabledForSite' => (bool)($state['enabledForSite'] ?? true),
            ];
        }

        return [
            'enabled' => true,
            'enabledForSite' => true,
        ];
    }

    public function setPendingDeleteRestoreState(bool $enabled, bool $enabledForSite): void
    {
        $this->data[self::PENDING_DELETE_STATE_DATA_KEY] = [
            'enabled' => $enabled,
            'enabledForSite' => $enabledForSite,
        ];
    }

    public function getIsPendingEdit(): bool
    {
        return !empty($this->data[self::PENDING_EDIT_DATA_KEY]);
    }

    public function setPendingEdit(bool $value = true): void
    {
        if ($value) {
            $this->data[self::PENDING_EDIT_DATA_KEY] = true;
        } else {
            unset($this->data[self::PENDING_EDIT_DATA_KEY]);
        }
    }

    public function clearPendingEdit(): void
    {
        $this->setPendingEdit(false);
    }

    public function getIsDisabledByLinkedElement(): bool
    {
        return !empty($this->data[self::LINKED_ELEMENT_DISABLED_DATA_KEY]);
    }

    public function setDisabledByLinkedElement(bool $value = true): void
    {
        if ($value) {
            $this->data[self::LINKED_ELEMENT_DISABLED_DATA_KEY] = true;
        } else {
            unset($this->data[self::LINKED_ELEMENT_DISABLED_DATA_KEY]);
        }
    }

    public function getLinkedElementDisabledRestoreState(): array
    {
        $state = $this->data[self::LINKED_ELEMENT_DISABLED_STATE_DATA_KEY] ?? null;

        if (is_array($state)) {
            return [
                'enabled' => (bool)($state['enabled'] ?? true),
                'enabledForSite' => (bool)($state['enabledForSite'] ?? true),
            ];
        }

        return [
            'enabled' => true,
            'enabledForSite' => true,
        ];
    }

    public function setLinkedElementDisabledRestoreState(bool $enabled, bool $enabledForSite): void
    {
        $this->data[self::LINKED_ELEMENT_DISABLED_STATE_DATA_KEY] = [
            'enabled' => $enabled,
            'enabledForSite' => $enabledForSite,
        ];
    }

    public function clearLinkedElementDisabledState(): void
    {
        $this->setDisabledByLinkedElement(false);
        unset($this->data[self::LINKED_ELEMENT_DISABLED_STATE_DATA_KEY]);
    }

    /**
     * Builder-only status indicator icon HTML for staged rows.
     */
    public function getBuilderPendingStatusIndicatorHtml(): ?string
    {
        $config = $this->_getBuilderPendingStatusConfig();

        if (!$config) {
            return null;
        }

        $classes = ['navigation-pending-status', 'navigation-pending-status--' . $config['key']];
        $aria = [
            'label' => sprintf('%s %s', Craft::t('app', 'Status:'), $config['label']),
        ];

        if (($config['iconType'] ?? 'craft') === 'fontawesome') {
            return Html::tag('span', '', [
                'class' => array_merge(['fa', 'fa-' . $config['icon']], $classes),
                'role' => 'img',
                'aria' => $aria,
            ]);
        }

        return Html::tag('span', '', [
            'data' => ['icon' => $config['icon']],
            'class' => array_merge(['icon'], $classes),
            'role' => 'img',
            'aria' => $aria,
        ]);
    }

    public function init(): void
    {
        parent::init();

        // Handle validation of max levels when dragging items across levels in structure
        Event::on(Structures::class, Structures::EVENT_BEFORE_MOVE_ELEMENT, function(MoveElementEvent $event) {
            if (!($event->element instanceof $this)) {
                return;
            }

            $nav = $event->element->_getMenu();

            // Check for max nodes at level. This was only added in Craft 4.5, so check
            if (property_exists($event, 'targetElementId')) {
                if ($nav->maxNodesSettings && $node = $event->getTargetElement()) {
                    Navigation::$plugin->getNodes()->setTempNodes([$node]);

                    if ($nav->isOverMaxLevel($node, $event->getTargetElement())) {
                        $event->isValid = false;
                    }
                }
            }
        });
    }

    public function createAnother(): ?self
    {
        $nav = $this->_getMenu();

        $node = Craft::createObject([
            'class' => self::class,
            'menuId' => $this->menuId,
            'siteId' => $this->siteId,
        ]);

        $node->enabled = $this->enabled;
        $node->setEnabledForSite($this->getEnabledForSite());

        // Structure parent
        if ($nav->maxLevels !== 1) {
            $node->setParentId($this->getParentId());
        }

        return $node;
    }

    public function canView(User $user): bool
    {
        return true;
    }

    public function canSave(User $user): bool
    {
        if ($this->getIsPendingDelete() && Navigation::$plugin->getBuildSessions()->isStagingEnabled()) {
            return false;
        }

        return true;
    }

    public function canDuplicate(User $user): bool
    {
        if ($this->getIsPendingDelete() && Navigation::$plugin->getBuildSessions()->isStagingEnabled()) {
            return false;
        }

        return true;
    }

    public function canDelete(User $user): bool
    {
        if ($this->getIsPendingDelete() && Navigation::$plugin->getBuildSessions()->isStagingEnabled()) {
            return false;
        }

        return true;
    }

    public function canCreateDrafts(User $user): bool
    {
        return true;
    }

    public function getStatus(): ?string
    {
        if ($this->getIsPendingPublish() && !$this->getIsDraft()) {
            return self::STATUS_PENDING_ADD;
        }

        if ($this->getIsPendingDelete() && !$this->getIsDraft()) {
            return self::STATUS_PENDING_DELETE;
        }

        if ($this->getIsPendingEdit() && !$this->getIsDraft()) {
            return self::STATUS_PENDING_EDIT;
        }

        return parent::getStatus();
    }

    public function getChipLabelHtml(): string
    {
        // Detect if this is the element index
        $isElementIndex = Craft::$app->getRequest()->getParam('viewState.mode') === 'table';

        // When reloading nodes, get the modified HTML
        if (Craft::$app->getRequest()->getSegments() === ['actions', 'app', 'render-elements']) {
            $isElementIndex = true;
        }

        // Only show this when editing the nav, in case these elements are listed by third parties
        if (!$isElementIndex) {
            return parent::getChipLabelHtml();
        }

        $title = $this->hasOverriddenTitle();
        $newWindow = $this->newWindow;
        $classes = $this->classes ? '.' . str_replace(' ', ' .', $this->classes) : '';

        $html = implode(' ', array_filter([
            $title ? Html::tag('span', '', ['class' => 'node-custom-title edit icon']) : false,
            $newWindow ? Html::tag('span', '', ['class' => 'node-new-window fa fa-external-link']) : false,
            $classes ? Html::tag('span', $classes, ['class' => 'node-classes classes code']) : false,
        ]));

        return parent::getChipLabelHtml() . ($html ? Html::tag('span', $html, ['class' => 'node-info-icons']) : '') . $this->_getBuilderRowActionBtnHtml();
    }

    public function getElement(): ?ElementInterface
    {
        if ($this->_element !== null) {
            return $this->_element;
        }

        // To prevent potentially nasty errors, check if this node is an appropriate element node type
        // Otherwise, in some rare scenarios where there's elementId info for a node, but a non-element node type
        // this can really go bananas.
        if (!$this->elementId || !$this->isElement()) {
            return null;
        }

        $nodeType = $this->nodeType();

        if (!$nodeType instanceof ElementNodeType) {
            return null;
        }

        return $this->_element = Craft::$app->getElements()->getElementById(
            $this->elementId,
            $nodeType::getElementType(),
            $this->getElementSiteId(),
        );
    }

    public function setElement($element = null): void
    {
        $this->_element = $element;
    }

    public function getElementSiteId(): ?int
    {
        if ($this->_linkedElementSiteId !== null) {
            return $this->_linkedElementSiteId;
        }

        if ($this->id && $this->siteId) {
            $settings = Navigation::$plugin->getNodeSites()->getSettings($this->id, $this->siteId);

            if ($settings?->linkedElementSiteId) {
                return $this->_linkedElementSiteId = $settings->linkedElementSiteId;
            }
        }

        return $this->siteId ?? Craft::$app->getSites()->getCurrentSite()->id;
    }

    public function setElementSiteId($value): void
    {
        $this->_linkedElementSiteId = $value ? (int)$value : null;
    }

    public function getElementSlug(): ?string
    {
        if ($element = $this->getElement()) {
            return $element->slug;
        }

        return '';
    }

    public function getCurrent(): bool
    {
        return Navigation::$plugin->getActiveMatcher()->isCurrent($this);
    }

    public function getActive($includeChildren = true): ?bool
    {
        $matcher = Navigation::$plugin->getActiveMatcher();
        $isActive = $includeChildren ? $matcher->isActive($this) : $matcher->isCurrent($this);

        // Allow plugins to modify this value
        $event = new NodeActiveEvent([
            'node' => $this,
            'isActive' => $isActive,
        ]);
        Event::trigger(static::class, self::EVENT_NODE_ACTIVE, $event);

        return $event->isActive;
    }

    public function getActiveState(): NodeActiveState
    {
        return $this->_activeState ?? new NodeActiveState();
    }

    public function hasResolvedActiveState(): bool
    {
        return $this->_activeStateResolved;
    }

    public function setActiveState(NodeActiveState $state): void
    {
        $this->_activeState = $state;
        $this->_activeStateResolved = true;
    }

    public function clearActiveState(): void
    {
        $this->_activeState = null;
        $this->_activeStateResolved = false;
    }

    public function getTag(): string
    {
        $nodeType = $this->nodeType();

        if ($nodeType) {
            return $nodeType::getTag();
        }

        return $this->getUrl() ? 'a' : 'span';
    }

    public function getMenu(): ?Menu
    {
        if ($this->menuId === null) {
            return null;
        }

        if ($this->_eagerLoadedMenu !== null || ($this->_eagerLoadedMenuResolved ?? false)) {
            return $this->_eagerLoadedMenu;
        }

        return Menu::find()
            ->id($this->menuId)
            ->siteId($this->siteId)
            ->status(null)
            ->one();
    }

    public function setEagerLoadedMenu(?Menu $menu): void
    {
        $this->_eagerLoadedMenu = $menu;
        $this->_eagerLoadedMenuResolved = true;
    }

    public function getRawElementUrl(): ?string
    {
        return $this->_elementUrl;
    }

    public function hasActiveChild(): bool
    {
        return Navigation::$plugin->getActiveMatcher()->hasActiveChild($this);
    }

    public function getRawUrl(): ?string
    {
        return $this->_url;
    }

    public function getUrl($includeSuffix = true): ?string
    {
        if ($this->nodeType()) {
            $url = $this->nodeType()->getUrl();
        } else if ($this->isElement()) {
            $url = $this->getElementUrl();
        } else {
            $url = $this->getRawUrl();
        }

        if ($this->urlSuffix && $includeSuffix) {
            $url .= $this->urlSuffix;
        }

        return $url;
    }

    /**
     * @inheritdoc
     */
    public function getPostEditUrl(): ?string
    {
        if (!$this->menuId) {
            return null;
        }

        $params = [];

        if (Craft::$app->getIsMultiSite()) {
            $site = Craft::$app->getSites()->getSiteById($this->siteId);

            if ($site) {
                $params['site'] = $site->handle;
            }
        }

        return UrlHelper::cpUrl('navigation/menus/build/' . $this->menuId, $params);
    }

    public function setUrl($value): void
    {
        if ($value === null || $value === '') {
            $this->_url = null;

            return;
        }

        // Leading/trailing whitespace breaks Craft CP’s `Html::_namespaceAttributes()` on preview
        // menu links (it splits `href` on whitespace; an empty first segment triggers a PHP notice).
        $trimmed = trim((string)$value);
        $this->_url = $trimmed !== '' ? $trimmed : null;
    }

    public function getElementUrl()
    {
        if ($this->_elementUrl !== null) {
            $path = ($this->_elementUrl === '__home__') ? '' : $this->_elementUrl;

            return UrlHelper::siteUrl($path, null, null, $this->getElementSiteId());
        }

        $element = $this->getElement();

        return $element->url ?? null;
    }

    public function setElementUrl($value): void
    {
        $this->_elementUrl = $value;
    }

    public function getNodeUri(): string
    {
        if ($url = $this->getUrl()) {
            return str_replace(UrlHelper::siteUrl('', null, null, $this->siteId), '', $url);
        }

        return '';
    }

    public function getLinkAttributes($extraAttributes = null): Markup
    {
        $object = $this->_getObject();
        $tag = $this->getTag();

        $classes = $this->classes ? Craft::$app->getView()->renderObjectTemplate($this->classes, $object) : null;

        // Passive / group / Dynamic nodes use getTag() (span by default) and must not emit a blank href.
        $attributes = [
            'href' => $tag === 'a' ? $this->getUrl() : null,
            'target' => $this->newWindow ? '_blank' : null,
            'rel' => $this->newWindow ? 'noopener' : null,
            'class' => $classes,
        ];

        foreach ($this->customAttributes as $attribute) {
            $key = $attribute['attribute'];
            $val = $attribute['value'];

            $attributes[$key] = Craft::$app->getView()->renderObjectTemplate($val, $object);
        }

        // Filter out any values
        $attributes = array_filter($attributes);

        if (is_array($extraAttributes)) {
            $attributes = array_merge_recursive($attributes, array_filter($extraAttributes));
        }

        return Template::raw(BaseHtml::renderTagAttributes($attributes));
    }

    public function getLink($attributes = null): ?Markup
    {
        $tag = $this->getTag() ?: 'a';

        return Template::raw('<' . $tag . ' ' . $this->getLinkAttributes($attributes) . '>' . Html::encode($this->__toString()) . '</' . $tag . '>');
    }

    public function getTarget(): string
    {
        return $this->newWindow ? '_blank' : '';
    }

    // Don't use `getNodeType()` due to an infinite loop issue, when appling this to registered nodes
    public function nodeType()
    {
        $typeClass = NodeTypeHelper::resolveTypeClass($this->type) ?? $this->type;

        $_nodeType = $this->_nodeTypes[$typeClass] ?? null;

        if ($_nodeType != null) {
            $_nodeType->node = $this;

            return $_nodeType;
        }

        $registeredNodeTypes = Navigation::$plugin->getNodeTypes()->getRegisteredNodeTypes();

        foreach ($registeredNodeTypes as $registeredNodeType) {
            if ($typeClass === $registeredNodeType::class) {
                $registeredNodeType->node = $this;

                return $this->_nodeTypes[$typeClass] = $registeredNodeType;
            }
        }

        return null;
    }

    public function getTypeLabel()
    {
        try {
            $nodeType = $this->nodeType();

            if ($nodeType instanceof ElementNodeType) {
                return Craft::t('site', $nodeType::getElementType()::displayName());
            }

            if ($nodeType) {
                return $nodeType->getTypeLabel();
            }

            if (class_exists($this->type)) {
                return $this->type::displayName();
            }
        } catch (Throwable $e) {
            // This will throw an error if the class exists, but the plugin disabled/uninstalled,
            // despite the check with `class_exists()` 
        }

        $classNameParts = explode('\\', $this->type);

        return array_pop($classNameParts);
    }

    public function getTypeLabelHtml(): string
    {
        $classNameParts = explode('\\', $this->type);
        $className = array_pop($classNameParts);
        $hexColor = $this->getTypeColorHex();

        $style = sprintf(
            '--node-type-color: %s; --node-type-text-color: %s;',
            NodeTypeHelper::rgbTriplet($hexColor),
            NodeTypeHelper::accessibleTextColorRgb($hexColor, 0.1),
        );

        $type = 'node-type-' . StringHelper::toKebabCase($className);
        $item = Html::tag('span', $this->getTypeLabel(), ['class' => $type, 'title' => $this->url]);

        return Html::tag('div', $item, ['class' => 'node-type', 'style' => $style]);
    }

    public function getTypeColor(): string
    {
        return NodeTypeHelper::rgbTriplet($this->getTypeColorHex());
    }

    public function getTypeColorHex(): string
    {
        $color = '#888888';

        try {
            $nodeType = $this->nodeType();

            if ($nodeType) {
                $color = $nodeType::getColor();
            } elseif (class_exists($this->type)) {
                $color = $this->type::getColor();
            }
        } catch (Throwable $e) {
        }

        return $color;
    }

    public function isElement(): bool
    {
        return $this->nodeType() instanceof ElementNodeType;
    }

    public function isCustom(): bool
    {
        return $this->type === Custom::class;
    }

    public function isPassive(): bool
    {
        return $this->type === Passive::class;
    }

    public function isGroupColumn(): bool
    {
        return $this->type === GroupColumn::class;
    }

    public function isSite(): bool
    {
        return $this->type === SiteNodeType::class;
    }

    public function getIsProjected(): bool
    {
        return false;
    }

    public function hasOverriddenTitle(): bool
    {
        $element = $this->getElement();

        return $element && $element->title !== $this->title;
    }

    public function getIsTitleTranslatable(): bool
    {
        return $this->_getMenu()->titleTranslationMethod !== Field::TRANSLATION_METHOD_NONE;
    }

    public function getTitleTranslationDescription(): ?string
    {
        return ElementHelper::translationDescription($this->_getMenu()->titleTranslationMethod);
    }

    public function getTitleTranslationKey(): string
    {
        $menu = $this->_getMenu();

        return ElementHelper::translationKey(
            $this,
            $menu->titleTranslationMethod,
            $menu->titleTranslationKeyFormat,
        );
    }

    public function getSupportedSites(): array
    {
        $nav = $this->_getMenu();

        /* @var Site[] $allSites */
        $allSites = ArrayHelper::index($nav->getSites(), 'id');
        $siteIds = [];

        foreach ($nav->getSiteSettings() as $siteSettings) {
            if ($siteSettings->enabled) {
                switch ($nav->propagationMethod) {
                    case MenuSettings::PROPAGATION_METHOD_NONE:
                        $include = $siteSettings->siteId == $this->siteId;
                        break;
                    case MenuSettings::PROPAGATION_METHOD_SITE_GROUP:
                        $include = $allSites[$siteSettings->siteId]->groupId == $allSites[$this->siteId]->groupId;
                        break;
                    case MenuSettings::PROPAGATION_METHOD_LANGUAGE:
                        $include = $allSites[$siteSettings->siteId]->language == $allSites[$this->siteId]->language;
                        break;
                    default:
                        $include = true;
                        break;
                }

                if ($include) {
                    $siteIds[] = $siteSettings->siteId;
                }
            }
        }

        return $siteIds;
    }

    public function getEnabledForPropagatedSitesPreference(?MenuSettings $menu = null): bool
    {
        $menu ??= $this->_getMenu();
        $data = is_array($this->data) ? $this->data : [];

        if (array_key_exists(self::ENABLED_FOR_PROPAGATED_SITES_DATA_KEY, $data)) {
            return (bool)$data[self::ENABLED_FOR_PROPAGATED_SITES_DATA_KEY];
        }

        return (bool)($menu->defaultEnabledForPropagatedSites ?? true);
    }

    public function setEnabledForPropagatedSitesPreference(bool $value): void
    {
        $data = is_array($this->data) ? $this->data : [];
        $data[self::ENABLED_FOR_PROPAGATED_SITES_DATA_KEY] = $value;
        $this->data = $data;
    }

    public function applyPropagationEnabledSiteStatuses(bool $enabledOnPropagatedSites, ?bool $enabledOnOwnerSite = null): void
    {
        $menu = $this->_getMenu();

        if (!$menu->getHasMultiSiteNodes()) {
            return;
        }

        $ownerSiteId = (int)$this->siteId;
        $enabledOnOwnerSite ??= $this->getEnabledForSite($ownerSiteId) ?? (bool)$this->enabled;
        $map = [];

        foreach ($this->getSupportedSites() as $site) {
            $siteId = is_array($site) ? (int)$site['siteId'] : (int)$site;
            $map[$siteId] = ($siteId === $ownerSiteId) ? $enabledOnOwnerSite : $enabledOnPropagatedSites;
        }

        if ($map === []) {
            return;
        }

        $this->enabled = true;
        $this->setEnabledForSite($map);
    }

    public function publishPendingAdd(): void
    {
        $menu = $this->_getMenu();

        if ($menu->getHasMultiSiteNodes()) {
            $this->applyPropagationEnabledSiteStatuses(
                $this->getEnabledForPropagatedSitesPreference($menu),
                true,
            );
        } else {
            $this->enabled = true;
            $this->setEnabledForSite(true);
        }

        $this->clearPendingPublish();
    }

    public function getGqlTypeName(): string
    {
        return static::gqlTypeNameByContext($this->_getMenu());
    }

    public function beforeSave(bool $isNew): bool
    {
        if (
            !$isNew
            && !$this->propagating
            && $this->getIsPendingDelete()
            && Navigation::$plugin->getBuildSessions()->isStagingEnabled()
        ) {
            $existing = Craft::$app->getElements()->getElementById($this->id, self::class, $this->siteId);

            if ($existing instanceof self && $existing->getIsPendingDelete()) {
                return false;
            }
        }

        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();

        $nav = $this->_getMenu();

        // Verify that the menu supports this site
        $navSiteSettings = $nav->getSiteSettings();

        $navSiteSetting = $navSiteSettings[$this->siteId] ?? null;

        if (!$navSiteSetting || !($navSiteSetting->enabled ?? false)) {
            throw new UnsupportedSiteException($this, $this->siteId, "The menu '$nav->name' is not enabled for the site '$this->siteId'");
        }

        // Set the structure ID for Element::attributes() and afterSave()
        $this->structureId = $nav->structureId;

        if (!$this->duplicateOf && $this->hasNewParent()) {
            if ($parentId = $this->getParentId()) {
                $parentNode = Navigation::$plugin->getNodes()->getNodeById($parentId, '*', [
                    'preferSites' => [$this->siteId],
                    'drafts' => null,
                    'draftOf' => false,
                ]);

                if (!$parentNode) {
                    throw new InvalidConfigException("Invalid node ID: $parentId");
                }
            } else {
                $parentNode = null;
            }

            $this->setParent($parentNode);
        }

        $resolvedType = NodeTypeHelper::resolveTypeClass($this->type);

        if ($resolvedType) {
            $this->type = $resolvedType;
        }

        // If this is propagating, we want to fetch the information for that site's linked element
        if ($this->propagating && $this->isElement() && $this->elementId) {
            $nodeType = $this->nodeType();
            $elementType = $nodeType instanceof ElementNodeType ? $nodeType::getElementType() : null;
            $localeElement = Craft::$app->getElements()->getElementById($this->elementId, $elementType, $this->siteId);

            if ($localeElement) {
                $this->setLinkedElementSiteId($localeElement->siteId);

                if (!$this->hasOverriddenTitle()) {
                    $this->title = $localeElement->title;
                }
            }
        }

        // If no title is set (for a custom node type for instance), generate one.
        if (!$this->title && $this->nodeType()) {
            $this->title = $this->nodeType()->getDefaultTitle();
        }

        if (!$this->isElement()) {
            $this->elementId = null;
            $this->setLinkedElementSiteId(null);
        }

        if ($this->nodeType()) {
            $this->nodeType()->beforeSaveNode($isNew);
        }

        if ($isNew && !$this->propagating && $nav->getHasMultiSiteNodes()) {
            $enabledOnPropagated = $this->getEnabledForPropagatedSitesPreference($nav);

            if ($this->getIsPendingPublish()) {
                $this->applyPropagationEnabledSiteStatuses(false, false);
            } else {
                $this->applyPropagationEnabledSiteStatuses($enabledOnPropagated, true);
            }
        }

        return parent::beforeSave($isNew);
    }

    public function afterSave(bool $isNew): void
    {
        if (!$this->propagating) {
            $nav = $this->_getMenu();

            // Get the node record
            if (!$isNew) {
                $record = NodeRecord::findOne($this->id);

                if (!$record) {
                    throw new InvalidConfigException("Invalid node ID: $this->id");
                }
            } else {
                $record = new NodeRecord();
                $record->id = (int)$this->id;
            }

            // Manual publish (slide-out, Enable, or Save menu) clears the deferred-builder flag.
            // Never clear on the creating save — add-nodes may still be writing `_pendingPublish`.
            if (!$isNew && $this->enabled && $this->getEnabledForSite()) {
                $this->clearPendingPublish();
            }

            $record->elementId = $this->elementId;
            $record->menuId = (int)$this->menuId;
            $record->url = null;
            $record->type = $this->type;
            $record->classes = $this->classes;
            $record->urlSuffix = null;
            $record->customAttributes = $this->customAttributes;
            $record->data = $this->data;
            $record->newWindow = $this->newWindow;

            // Capture the dirty attributes from the record
            $dirtyAttributes = array_keys($record->getDirtyAttributes());

            $record->save(false);

            Navigation::$plugin->getNodeSites()->saveFromNode($this);

            if ($this->getIsCanonical() && !$this->getIsUnpublishedDraft()) {
                if ($this->hasNewParent()) {
                    $this->_placeInStructure($isNew, $nav);
                }
            }

            $this->setDirtyAttributes($dirtyAttributes);
        }

        parent::afterSave($isNew);
    }

    public function beforeDelete(): bool
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // Update the node record
        $data = [
            'deletedWithMenu' => $this->deletedWithMenu,
            'parentId' => null,
        ];

        if ($this->structureId) {
            // Remember the parent ID, in case the node needs to be restored later
            $parentId = $this->getAncestors(1)
                ->status(null)
                ->select(['elements.id'])
                ->scalar();

            if ($parentId) {
                $data['parentId'] = $parentId;
            }
        }

        Db::update('{{%navigation_nodes}}', $data, [
            'id' => $this->id,
        ], [], false);

        return true;
    }

    public function afterRestore(): void
    {
        $nav = $this->_getMenu();
        $structureId = (int)$nav->structureId;

        $parentId = (new Query())
            ->select(['parentId'])
            ->from(['{{%navigation_nodes}}'])
            ->where(['id' => $this->id])
            ->scalar();

        $structuresService = Craft::$app->getStructures();

        if ($parentId) {
            $parentInStructure = (new Query())
                ->from(['{{%structureelements}}'])
                ->where([
                    'structureId' => $structureId,
                    'elementId' => $parentId,
                ])
                ->exists();

            if ($parentInStructure) {
                $parent = self::find()->id($parentId)->status(null)->one();

                if ($parent) {
                    $structuresService->append($structureId, $this, $parent);
                    parent::afterRestore();

                    return;
                }
            }
        }

        $structuresService->appendToRoot($structureId, $this);

        parent::afterRestore();
    }

    public function afterMoveInStructure(int $structureId): void
    {
        // Was the node moved within its group's structure?
        $nav = $this->_getMenu();

        if ($nav->structureId == $structureId) {
            Craft::$app->getElements()->updateElementSlugAndUri($this, true, true, true);

            // If this is the canonical node, update its drafts
            if ($this->getIsCanonical()) {
                /* @var self[] $drafts */
                $drafts = self::find()
                    ->draftOf($this)
                    ->status(null)
                    ->site('*')
                    ->unique()
                    ->all();

                $structuresService = Craft::$app->getStructures();
                $lastElement = $this;

                foreach ($drafts as $draft) {
                    $structuresService->moveAfter($nav->structureId, $draft, $lastElement);
                    $lastElement = $draft;
                }
            }
        }

        parent::afterMoveInStructure($structureId);
    }

    public function getFieldLayout(): ?FieldLayout
    {
        $nav = $this->menuId === null ? null : $this->_getMenu();

        return $nav ? $nav->getFieldLayout() : null;
    }

    public function getCustomAttributesObject(): array
    {
        $object = [];

        foreach ($this->customAttributes as $attribute) {
            // Normalize some attributes
            if ($attribute['attribute'] === 'class' && !is_array($attribute['value'])) {
                $attribute['value'] = [$attribute['value']];
            }

            $object[$attribute['attribute']] = $attribute['value'];
        }

        return array_filter($object);
    }

    public function getLinkedElementId(): ?int
    {
        return $this->elementId;
    }

    public function setLinkedElementId($value): void
    {
        // This is a required proxy variable when editing a node, due to a conflicting `elementId`.
        if (is_array($value)) {
            $this->elementId = $value[0];
        } else {
            $this->elementId = (int)$value;
        }

        // Also check for `0` (string or int) and set correct value for type
        if (!$this->elementId) {
            $this->elementId = null;
        }
    }

    public function setLinkedElementSiteId($value): void
    {
        if ($value) {
            $this->elementSiteId = (int)$value;
        }
    }

    public function _getActive($includeChildren = true): bool
    {
        $matcher = Navigation::$plugin->getActiveMatcher();

        return $includeChildren ? $matcher->isActive($this) : $matcher->isCurrent($this);
    }

    // Protected Methods
    // =========================================================================

    protected function metadata(): array
    {
        $config = $this->_getBuilderPendingStatusConfig();

        if (!$config || $this->getIsDraft()) {
            return [];
        }

        return [
            Craft::t('app', 'Status') => function() use ($config) {
                return Html::tag('span', '', [
                    'data' => ['icon' => $config['icon']],
                    'class' => 'icon',
                    'aria' => ['hidden' => 'true'],
                ]) . Html::tag('span', $config['label']);
            },
        ];
    }

    protected function _getMenu(): MenuSettings
    {
        if ($this->menuId === null) {
            throw new InvalidConfigException('Node is missing its menu ID');
        }

        $nav = Navigation::$plugin->getMenus()->getMenuById($this->menuId);

        if (!$nav) {
            throw new InvalidConfigException('Invalid menu ID: ' . $this->menuId);
        }

        return $nav;
    }

    /**
     * @inheritdoc
     */
    protected function crumbs(): array
    {
        if ($this->menuId === null) {
            return [];
        }

        $nav = Navigation::$plugin->getMenus()->getMenuById($this->menuId);

        if (!$nav) {
            return [];
        }

        $params = [];

        if (Craft::$app->getIsMultiSite()) {
            $site = Craft::$app->getSites()->getSiteById($this->siteId);

            if ($site) {
                $params['site'] = $site->handle;
            }
        }

        return [
            [
                'label' => Craft::t('navigation', 'Menus'),
                'url' => UrlHelper::cpUrl('navigation/menus'),
            ],
            [
                'label' => Craft::t('site', $nav->name),
                'url' => UrlHelper::cpUrl('navigation/menus/build/' . $nav->id, $params),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function cpEditUrl(): ?string
    {
        return ElementHelper::elementEditorUrl($this, false);
    }

    /**
     * Nodes resolve front-end URLs from linked elements or custom paths, not a dedicated
     * node route. Craft’s default preview target would tokenize that URL and mislead the
     * builder slide-out; full-page editing (and entry previews) belong on the CP edit screen.
     *
     * @inheritdoc
     */
    protected function previewTargets(): array
    {
        return [];
    }

    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        // Must be included to allow `setAttributes()` to work, and treat it as safe. This is so the element
        // slide-out can update the type for draft-changes.
        $rules[] = [['linkedElementId', 'linkedElementSiteId', 'url', 'urlSuffix', 'classes', 'newWindow', 'customAttributes', 'type', 'data', 'parentId'], 'safe'];

        $rules[] = [
            'level',
            function($attribute, $params, Validator $validator): void {
                $nav = $this->_getMenu();

                // Check for max nodes
                if ($nav->maxNodes) {
                    if ($nav->isOverMaxNodes($this)) {
                        $validator->addError($this, $attribute, Craft::t('navigation', 'Exceeded maximum allowed nodes ({number}) for this menu.', ['number' => $nav->maxNodes]));
                    }
                }

                // Check for max nodes at level
                if ($nav->maxNodesSettings) {
                    // Populate the level, because it's not in POST data
                    $this->level = $this->getParent() ? ($this->getParent()->level + 1) : 1;

                    if ($nav->isOverMaxLevel($this)) {
                        $validator->addError($this, $attribute, Craft::t('navigation', 'Exceeded maximum allowed nodes for this level.'));
                    }
                }
            },
            'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_LIVE, self::SCENARIO_ESSENTIALS],
        ];

        $rules[] = [
            'elementId',
            function($attribute, $params, Validator $validator): void {
                // Don't check if this is a draft, likely just switched to different node type
                if (!$this->getIsDraft() && $this->isElement() && empty($this->elementId)) {
                    // Add to both attributes as the element slide-out uses `linkedElementId`
                    $validator->addError($this, 'elementId', Craft::t('navigation', 'Element ID is required.'));
                    $validator->addError($this, 'linkedElementId', Craft::t('navigation', 'Linked Element ID is required.'));
                }
            },
            'skipOnEmpty' => false,
            'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_LIVE, self::SCENARIO_ESSENTIALS],
        ];

        return $rules;
    }

    protected function attributeHtml(string $attribute): string
    {
        if ($attribute == 'typeLabel') {
            return $this->getTypeLabelHtml();
        } else if ($attribute == 'actions') {
            $tags = Html::tag('a', null, ['class' => 'settings icon', 'title' => 'Settings']) . Html::tag('a', null, ['class' => 'delete icon', 'title' => 'Delete']);

            return Html::tag('div', $tags);
        }

        return parent::attributeHtml($attribute);
    }

    protected function metaFieldsHtml(bool $static): string
    {
        $nav = $this->_getMenu();
        
        $fields = [];

        // Type
        $fields[] = (function() use ($static) {
            $nodeTypeOptions = [];

            foreach (Navigation::$plugin->getMenus()->getBuilderTabs($this->_getMenu()) as $tab) {
                $nodeTypeOptions[] = [
                    'label' => Craft::t('site', $tab['label']),
                    'value' => $tab['type'],
                ];
            }

            $view = Craft::$app->getView();
            $typeInputId = $view->namespaceInputId('type');
            $js = <<<EOD
(() => {
const \$typeInput = $('#$typeInputId');
const getEditor = () => {
    const \$editorContainer = \$typeInput.closest('[data-element-editor]');
    if (\$editorContainer.length) {
        return \$editorContainer.data('elementEditor');
    }
    return \$typeInput.closest('form').data('elementEditor');
};

\$typeInput.on('change', () => {
    const editor = getEditor();
    if (editor) {
        editor.checkForm(true);
    }
});
})();
EOD;
            $view->registerJs($js);

            return Cp::selectFieldHtml([
                'label' => Craft::t('navigation', 'Type'),
                'id' => 'type',
                'name' => 'type',
                'value' => $this->type,
                'options' => $nodeTypeOptions,
            ]);
        })();

        if ($nav->maxLevels !== 1) {
            $fields[] = (function() use ($static) {
                if ($parentId = $this->getParentId()) {
                    $parent = Navigation::$plugin->getNodes()->getNodeById($parentId, $this->siteId);
                } else {
                    // If the node already has structure data, use it. Otherwise, use its canonical node
                    /* @var self|null $parent */
                    $parent = self::find()
                        ->siteId($this->siteId)
                        ->ancestorOf($this->lft ? $this : ($this->getIsCanonical() ? $this->id : $this->getCanonical(true)))
                        ->ancestorDist(1)
                        ->drafts(null)
                        ->draftOf(false)
                        ->status(null)
                        ->one();
                }

                $nav = $this->_getMenu();

                return Cp::elementSelectFieldHtml([
                    'label' => Craft::t('app', 'Parent'),
                    'id' => 'parentId',
                    'name' => 'parentId',
                    'elementType' => self::class,
                    'selectionLabel' => Craft::t('app', 'Choose'),
                    'sources' => ["menu:$nav->uid"],
                    'criteria' => $this->_parentOptionCriteria($nav),
                    'limit' => 1,
                    'elements' => $parent ? [$parent] : [],
                    'disabled' => $static,
                ]);
            })();
        }

        $fields[] = parent::metaFieldsHtml($static);

        return implode("\n", $fields);
    }
    

    // Private Methods
    // =========================================================================

    private function _getBuilderRowActionBtnHtml(): string
    {
        if ($this->getIsPendingDelete() && Navigation::$plugin->getBuildSessions()->isStagingEnabled()) {
            return Html::tag('a', Craft::t('navigation', 'Restore'), [
                'class' => 'btn small icon undo node-restore-btn',
            ]);
        }

        return Html::tag('a', Craft::t('navigation', 'Edit'), ['class' => 'btn small icon edit node-edit-btn']);
    }

    private function _getBuilderPendingStatusConfig(): ?array
    {
        if ($this->getIsPendingPublish() && !$this->getIsDraft()) {
            return [
                'key' => 'add',
                'icon' => 'plus-circle',
                'iconType' => 'fontawesome',
                'label' => Craft::t('navigation', 'Pending'),
            ];
        }

        if ($this->getIsPendingDelete() && !$this->getIsDraft()) {
            return [
                'key' => 'delete',
                'icon' => 'trash',
                'label' => Craft::t('navigation', 'Pending deletion'),
            ];
        }

        if ($this->getIsPendingEdit() && !$this->getIsDraft()) {
            return [
                'key' => 'edit',
                'icon' => 'pen-circle',
                'label' => Craft::t('navigation', 'Pending edit'),
            ];
        }

        return null;
    }

    private function _getObject(): array
    {
        return [
            'currentUser' => Craft::$app->getUser()->getIdentity(),
        ];
    }

    private function _parentOptionCriteria(MenuSettings $nav): array
    {
        $parentOptionCriteria = [
            'siteId' => $this->siteId,
            'menuId' => $nav->id,
            'status' => null,
            'drafts' => null,
            'draftOf' => false,
        ];

        // Prevent the current node, or any of its descendants, from being selected as a parent
        if ($this->id) {
            $excludeIds = self::find()
                ->descendantOf($this)
                ->drafts(null)
                ->draftOf(false)
                ->status(null)
                ->ids();

            $excludeIds[] = $this->getCanonicalId();
            $parentOptionCriteria['id'] = array_merge(['not'], $excludeIds);
        }

        if ($nav->maxLevels) {
            if ($this->id) {
                // Figure out how deep the ancestors go
                $maxDepth = self::find()
                    ->select('level')
                    ->descendantOf($this)
                    ->status(null)
                    ->leaves()
                    ->scalar();
                $depth = 1 + ($maxDepth ?: $this->level) - $this->level;
            } else {
                $depth = 1;
            }

            $parentOptionCriteria['level'] = sprintf('<=%s', $nav->maxLevels - $depth);
        }

        return $parentOptionCriteria;
    }

    private function _placeInStructure(bool $isNew, MenuSettings $nav): void
    {
        $parentId = $this->getParentId();
        $structuresService = Craft::$app->getStructures();

        // If this is a provisional draft and its new parent matches the canonical node’s, just drop it from the structure
        if ($this->isProvisionalDraft) {
            $canonicalParentId = self::find()
                ->select(['elements.id'])
                ->ancestorOf($this->getCanonicalId())
                ->ancestorDist(1)
                ->status(null)
                ->scalar();

            if ($parentId == $canonicalParentId) {
                $structuresService->remove($this->structureId, $this);
                return;
            }
        }

        $mode = $isNew ? Structures::MODE_INSERT : Structures::MODE_AUTO;

        if (!$parentId) {
            if ($nav->defaultPlacement === MenuSettings::DEFAULT_PLACEMENT_BEGINNING) {
                $structuresService->prependToRoot($this->structureId, $this, $mode);
            } else {
                $structuresService->appendToRoot($this->structureId, $this, $mode);
            }
        } else {
            if ($nav->defaultPlacement === MenuSettings::DEFAULT_PLACEMENT_BEGINNING) {
                $structuresService->prepend($this->structureId, $this, $this->getParent(), $mode);
            } else {
                $structuresService->append($this->structureId, $this, $this->getParent(), $mode);
            }
        }
    }
}
