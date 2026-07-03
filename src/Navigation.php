<?php
namespace verbb\navigation;

use verbb\navigation\base\PluginTrait;
use verbb\navigation\deprecations\LegacyBootstrap;
use verbb\navigation\elements\Menu;
use verbb\navigation\elements\Node;
use verbb\navigation\fields\NavigationField;
use verbb\navigation\fieldlayoutelements\ClassesField;
use verbb\navigation\fieldlayoutelements\CustomAttributesField;
use verbb\navigation\fieldlayoutelements\NewWindowField;
use verbb\navigation\fieldlayoutelements\NodeTypeElements;
use verbb\navigation\fieldlayoutelements\UrlSuffixField;
use verbb\navigation\gql\interfaces\NodeInterface;
use verbb\navigation\gql\queries\MenuQuery as MenuGqlQuery;
use verbb\navigation\gql\queries\NodeQuery;
use verbb\navigation\gql\types\generators\MenuGenerator;
use verbb\navigation\gql\types\ProjectedNodeType;
use verbb\navigation\helpers\BuilderUi;
use verbb\navigation\helpers\Gql as GqlHelper;
use verbb\navigation\helpers\ProjectConfigData;
use verbb\navigation\integrations\NodeFeedMeElement;
use verbb\navigation\models\Settings;
use verbb\navigation\services\Menus;
use verbb\navigation\variables\NavigationVariable;

use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\console\Controller as ConsoleController;
use craft\console\controllers\ResaveController;
use craft\events\DefineConsoleActionsEvent;
use craft\events\DefineFieldLayoutFieldsEvent;
use craft\events\RebuildConfigEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterGqlQueriesEvent;
use craft\events\RegisterGqlSchemaComponentsEvent;
use craft\events\RegisterGqlTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\events\SiteEvent;
use craft\events\ConfigEvent;
use craft\fieldlayoutelements\TitleField;
use craft\helpers\Cp;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use craft\services\Categories;
use craft\services\Elements;
use craft\services\Entries;
use craft\services\Fields;
use craft\services\Gql;
use craft\services\ProjectConfig;
use craft\services\Sites;
use craft\services\Structures;
use craft\services\UserPermissions;
use craft\services\Volumes;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;

use craft\feedme\events\RegisterFeedMeElementsEvent;
use craft\feedme\services\Elements as FeedMeElements;
use craft\gatsbyhelper\events\RegisterSourceNodeTypesEvent;
use craft\gatsbyhelper\services\SourceNodes;

class Navigation extends Plugin
{
    // Traits
    // =========================================================================

    use PluginTrait;


    // Properties
    // =========================================================================

    public bool $hasCpSection = true;
    public bool $hasCpSettings = true;
    public string $schemaVersion = '4.0.6';
    public string $minVersionRequired = '1.4.24';


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        LegacyBootstrap::init();

        parent::init();

        self::$plugin = $this;

        $this->_registerVariables();
        $this->_registerEventHandlers();
        $this->_registerProjectConfigEventHandlers();
        $this->_registerFieldTypes();
        $this->_registerElementTypes();
        $this->_registerGraphQl();
        $this->_registerFeedMeSupport();

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->_registerCpRoutes();
            $this->_registerFieldLayoutListener();
        }

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->_registerResaveCommand();
        }

        if (Craft::$app->getEdition() !== Craft::Solo) {
            $this->_registerPermissions();
        }
    }

    public function getPluginName(): string
    {
        return Craft::t('navigation', $this->getSettings()->pluginName);
    }

    public function getSettingsResponse(): mixed
    {
        return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('navigation/settings'));
    }

    public function getCpNavItem(): ?array
    {
        $nav = parent::getCpNavItem();
        $nav['label'] = $this->getPluginName();

        $nav['subnav']['menus'] = [
            'label' => Craft::t('navigation', 'Menus'),
            'url' => 'navigation/menus',
        ];

        if (Craft::$app->getUser()->getIsAdmin() && Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            $nav['subnav']['settings'] = [
                'label' => Craft::t('navigation', 'Settings'),
                'url' => 'navigation/settings',
            ];
        }

        return $nav;
    }


    // Protected Methods
    // =========================================================================

    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }


    // Private Methods
    // =========================================================================

    private function _registerCpRoutes(): void
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'navigation' => 'navigation/menus/index',
                'navigation/menus' => 'navigation/menus/index',
                'navigation/menus/new' => 'navigation/menus/edit-menu',
                'navigation/menus/edit/<menuId:\d+>' => 'navigation/menus/edit-menu',
                'navigation/menus/build/<menuId:\d+>' => 'navigation/menus/build-menu',
                'navigation/builder/get-state' => 'navigation/builder/get-state',
                'navigation/builder/save-draft' => 'navigation/builder/save-draft',
                'navigation/builder/stage-delete' => 'navigation/builder/stage-delete',
                'navigation/builder/set-node-status' => 'navigation/builder/set-node-status',
                'navigation/builder/duplicate-nodes' => 'navigation/builder/duplicate-nodes',
                'navigation/builder/menu-content-form' => 'navigation/builder/menu-content-form',
                'navigation/builder/menu-content-slideout' => 'navigation/builder/menu-content-slideout',
                'navigation/builder/menu-content-slideout-save' => 'navigation/builder/menu-content-slideout-save',
                'navigation/builder/save-menu-content' => 'navigation/builder/save-menu-content',
                'navigation/settings/import-export' => 'navigation/import-export/index',
                'navigation/settings/import-export/import-configure/<filename:[\w\-\.]+>' => 'navigation/import-export/import-configure',
                'navigation/settings/import-export/import-completed/<menuId:\d+>' => 'navigation/import-export/import-completed',
                'navigation/settings/migrate/<sourceId:[\w\-]+>' => 'navigation/migrate/index',
                'navigation/settings/performance' => 'navigation/base/settings',
                'navigation/settings' => 'navigation/base/settings',
                'navigation/migrate/<sourceId:[\w\-]+>' => 'navigation/migrate/run',
            ]);
        });
    }

    private function _registerVariables(): void
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $event->sender->set('navigation', NavigationVariable::class);
        });
    }

    private function _registerEventHandlers(): void
    {
        // Allow elements to update our nodes
        Event::on(Elements::class, Elements::EVENT_BEFORE_SAVE_ELEMENT, [$this->getNodes(), 'onSaveElement']);
        Event::on(Elements::class, Elements::EVENT_BEFORE_DELETE_ELEMENT, [$this->getNodes(), 'onBeforeDeleteElement']);
        Event::on(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT, [$this->getNodes(), 'onAfterSaveNode']);
        Event::on(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT, [$this->getNodes(), 'onAfterSaveProjectedContent']);
        Event::on(Elements::class, Elements::EVENT_AFTER_DELETE_ELEMENT, [$this->getNodes(), 'onDeleteElement']);
        Event::on(Elements::class, Elements::EVENT_AFTER_DELETE_ELEMENT, [$this->getNodes(), 'onAfterDeleteNode']);
        Event::on(Elements::class, Elements::EVENT_AFTER_DELETE_ELEMENT, [$this->getNodes(), 'onAfterDeleteProjectedContent']);
        Event::on(Elements::class, Elements::EVENT_AFTER_RESTORE_ELEMENT, [$this->getNodes(), 'onRestoreElement']);
        Event::on(Entries::class, Entries::EVENT_AFTER_DELETE_SECTION, [$this->getNodes(), 'onDeleteSection']);
        Event::on(Categories::class, Categories::EVENT_AFTER_DELETE_GROUP, [$this->getNodes(), 'onDeleteCategoryGroup']);
        Event::on(Volumes::class, Volumes::EVENT_AFTER_DELETE_VOLUME, [$this->getNodes(), 'onDeleteVolume']);

        $this->_registerCommerceEventHandlers();

        // Prune deleted fields from nav
        Event::on(Fields::class, Fields::EVENT_AFTER_DELETE_FIELD, [$this->getMenus(), 'pruneDeletedField']);

        // Prune deleted sites from site settings
        Event::on(Sites::class, Sites::EVENT_AFTER_DELETE_SITE, [$this->getMenus(), 'pruneDeletedSite']);
        Event::on(Sites::class, Sites::EVENT_AFTER_SAVE_SITE, [$this->getMenus(), 'afterSaveSite']);

        // Handle validation of max levels when dragging items across levels in structure
        Event::on(Structures::class, Structures::EVENT_BEFORE_MOVE_ELEMENT, [$this->getNodes(), 'onMoveElement']);

        Event::on(Cp::class, Cp::EVENT_DEFINE_ELEMENT_CHIP_HTML, [BuilderUi::class, 'onDefineElementChipHtml']);
    }

    private function _registerProjectConfigEventHandlers(): void
    {
        Craft::$app->getProjectConfig()
            ->onAdd(Menus::CONFIG_MENU_KEY . '.{uid}', [$this->getMenus(), 'handleChangedMenu'])
            ->onUpdate(Menus::CONFIG_MENU_KEY . '.{uid}', [$this->getMenus(), 'handleChangedMenu'])
            ->onRemove(Menus::CONFIG_MENU_KEY . '.{uid}', [$this->getMenus(), 'handleDeletedMenu']);

        Event::on(ProjectConfig::class, ProjectConfig::EVENT_REBUILD, function(RebuildConfigEvent $event) {
            $event->config['navigation'] = ProjectConfigData::rebuildProjectConfig();
        });
    }

    private function _registerFieldTypes(): void
    {
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = NavigationField::class;
        });
    }

    private function _registerElementTypes(): void
    {
        Event::on(Elements::class, Elements::EVENT_REGISTER_ELEMENT_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Node::class;
            $event->types[] = Menu::class;
        });
    }

    private function _registerPermissions(): void
    {
        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $navs = $this->getMenus()->getAllMenus();

            $navPermissions = [];

            $navPermissions['navigation-createMenus'] = [
                'label' => Craft::t('navigation', 'Create menus'),
            ];

            foreach ($navs as $nav) {
                $navPermissions['navigation-manageMenu:' . $nav->uid] = [
                    'label' => Craft::t('navigation', 'Manage “{type}”', ['type' => $nav->name]),
                    'nested' => [
                        'navigation-editMenu:' . $nav->uid => [
                            'label' => Craft::t('navigation', 'Edit menu settings'),
                        ],
                        'navigation-deleteMenu:' . $nav->uid => [
                            'label' => Craft::t('navigation', 'Delete menu'),
                        ],
                    ],
                ];
            }

            $event->permissions[] = [
                'heading' => Craft::t('navigation', 'Navigation'),
                'permissions' => $navPermissions,
            ];
        });
    }

    private function _registerGraphQl(): void
    {
        Event::on(Gql::class, Gql::EVENT_REGISTER_GQL_TYPES, function(RegisterGqlTypesEvent $event) {
            $event->types[] = NodeInterface::class;
            $event->types[] = ProjectedNodeType::class;
            ProjectedNodeType::getType();
            MenuGenerator::generateTypes();
        });

        Event::on(Gql::class, Gql::EVENT_REGISTER_GQL_QUERIES, function(RegisterGqlQueriesEvent $event) {
            $queries = NodeQuery::getQueries();

            foreach ($queries as $key => $value) {
                $event->queries[$key] = $value;
            }

            foreach (MenuGqlQuery::getQueries() as $key => $value) {
                $event->queries[$key] = $value;
            }
        });

        Event::on(Gql::class, Gql::EVENT_REGISTER_GQL_SCHEMA_COMPONENTS, function(RegisterGqlSchemaComponentsEvent $event) {
            $navs = Navigation::$plugin->getMenus()->getAllMenus();

            if (!empty($navs)) {
                $label = Craft::t('navigation', 'Navigation');
                $event->queries[$label]['navigationMenus.all:read'] = ['label' => Craft::t('navigation', 'View all menus')];

                foreach ($navs as $nav) {
                    $suffix = 'navigationMenus.' . $nav->uid;

                    $event->queries[$label][$suffix . ':read'] = [
                        'label' => Craft::t('navigation', 'View menu - {menu}', ['menu' => Craft::t('site', $nav->name)]),
                    ];
                }
            }
        });

        if (class_exists(SourceNodes::class)) {
            Event::on(SourceNodes::class, SourceNodes::EVENT_REGISTER_SOURCE_NODE_TYPES, function(RegisterSourceNodeTypesEvent $event) {
                if (GqlHelper::canQueryNavigation()) {
                    $event->types[NodeInterface::getName()] = [
                        'node' => 'navigationNode',
                        'list' => 'navigationNodes',
                        'filterArgument' => '',
                        'filterTypeExpression' => '(.+)_Node',
                        'targetInterface' => NodeInterface::getName(),
                    ];
                }
            });
        }
    }

    private function _registerFeedMeSupport(): void
    {
        if (class_exists(FeedMeElements::class)) {
            Event::on(FeedMeElements::class, FeedMeElements::EVENT_REGISTER_FEED_ME_ELEMENTS, function(RegisterFeedMeElementsEvent $event) {
                $event->elements[] = NodeFeedMeElement::class;
            });
        }
    }

    private function _registerResaveCommand(): void
    {
        if (!Craft::$app instanceof ConsoleApplication) {
            return;
        }

        Event::on(ResaveController::class, ConsoleController::EVENT_DEFINE_ACTIONS, function(DefineConsoleActionsEvent $event) {
            $event->actions['navigation-nodes'] = [
                'action' => function(): int {
                    $controller = Craft::$app->controller;
                    $menuId = $controller->menuId ?? $controller->navId ?? null;

                    if ($controller->navId !== null && $controller->menuId === null) {
                        // Deprecated in 4.0.0
                        Craft::$app->getDeprecator()->log(
                            'navigation.console.resave.navId',
                            'The `--navId` option has been deprecated. Use `--menuId` instead.',
                        );
                    }

                    $criteria = [];

                    if ($menuId !== null) {
                        $criteria['menuId'] = explode(',', $menuId);
                    }

                    return $controller->resaveElements(Node::class, $criteria);
                },
                'options' => ['menuId', 'navId'],
                'helpSummary' => 'Re-saves Navigation nodes.',
                'optionsHelp' => [
                    'menuId' => 'The menu ID of the nodes to resave.',
                    'navId' => 'Deprecated. Use menuId instead.',
                ],
            ];
        });
    }

    private function _registerFieldLayoutListener(): void
    {
        Event::on(FieldLayout::class, FieldLayout::EVENT_DEFINE_NATIVE_FIELDS, function(DefineFieldLayoutFieldsEvent $event) {
            if ($event->sender->type === Node::class) {
                $event->fields[] = TitleField::class;
                $event->fields[] = UrlSuffixField::class;
                $event->fields[] = ClassesField::class;
                $event->fields[] = NewWindowField::class;
                $event->fields[] = CustomAttributesField::class;
                $event->fields[] = NodeTypeElements::class;
            }
        });
    }

    private function _registerCommerceEventHandlers(): void
    {
        if (!Craft::$app->getPlugins()->isPluginEnabled('commerce') || !class_exists('craft\\commerce\\Plugin')) {
            return;
        }

        Craft::$app->getProjectConfig()->onRemove('commerce.productTypes.{uid}', function(ConfigEvent $event): void {
            $uid = $event->tokenMatches[0] ?? null;

            if (!$uid) {
                return;
            }

            $productType = \craft\commerce\Plugin::getInstance()->getProductTypes()->getProductTypeByUid($uid);

            if (!$productType && is_array($event->oldValue) && !empty($event->oldValue['id'])) {
                $this->getNodes()->onDeleteProductType((int)$event->oldValue['id']);

                return;
            }

            if ($productType) {
                $this->getNodes()->onDeleteProductType((int)$productType->id);
            }
        });
    }
}
