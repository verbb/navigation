<?php
namespace verbb\navigation;

use verbb\navigation\base\PluginTrait;
use verbb\navigation\elements\Node;
use verbb\navigation\fields\NavigationField;
use verbb\navigation\fieldlayoutelements\ClassesField;
use verbb\navigation\fieldlayoutelements\CustomAttributesField;
use verbb\navigation\fieldlayoutelements\NewWindowField;
use verbb\navigation\fieldlayoutelements\NodeTypeElements;
use verbb\navigation\fieldlayoutelements\UrlSuffixField;
use verbb\navigation\gql\interfaces\NodeInterface;
use verbb\navigation\gql\queries\NodeQuery;
use verbb\navigation\helpers\Gql as GqlHelper;
use verbb\navigation\helpers\ProjectConfigData;
use verbb\navigation\integrations\NodeFeedMeElement;
use verbb\navigation\models\Settings;
use verbb\navigation\services\Navs;
use verbb\navigation\twigextensions\Extension;
use verbb\navigation\variables\NavigationVariable;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\console\Controller as ConsoleController;
use craft\console\controllers\ResaveController;
use craft\events\ConfigEvent;
use craft\events\DefineConsoleActionsEvent;
use craft\events\DefineFieldLayoutFieldsEvent;
use craft\events\RebuildConfigEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterGqlQueriesEvent;
use craft\events\RegisterGqlSchemaComponentsEvent;
use craft\events\RegisterGqlTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\fieldlayoutelements\TitleField;
use craft\helpers\Cp;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Gql;
use craft\services\ProjectConfig;
use craft\services\Sites;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;

use craft\feedme\events\RegisterFeedMeElementsEvent;
use craft\feedme\services\Elements as FeedMeElements;

use craft\gatsbyhelper\events\RegisterSourceNodeTypesEvent;
use craft\gatsbyhelper\services\SourceNodes;

class Navigation extends Plugin
{
    // Properties
    // =========================================================================

    public bool $hasCpSection = true;
    public bool $hasCpSettings = true;
    public string $schemaVersion = '2.0.7';
    public string $minVersionRequired = '1.4.24';


    // Traits
    // =========================================================================

    use PluginTrait;


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        parent::init();

        self::$plugin = $this;

        $this->_registerComponents();
        $this->_registerLogTarget();
        $this->_registerVariables();
        $this->_registerCraftEventListeners();
        $this->_registerProjectConfigEventListeners();
        $this->_registerTwigExtensions();
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

        if (Craft::$app->getEdition() === Craft::Pro) {
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
        $navItem = parent::getCpNavItem();
        $navItem['label'] = $this->getPluginName();

        return $navItem;
    }


    // Protected Methods
    // =========================================================================

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }


    // Private Methods
    // =========================================================================

    private function _registerTwigExtensions(): void
    {
        Craft::$app->getView()->registerTwigExtension(new Extension);
    }

    private function _registerCpRoutes(): void
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'navigation' => 'navigation/navs/index',
                'navigation/navs' => 'navigation/navs/index',
                'navigation/navs/new' => 'navigation/navs/edit-nav',
                'navigation/navs/edit/<navId:\d+>' => 'navigation/navs/edit-nav',
                'navigation/navs/build/<navId:\d+>' => 'navigation/navs/build-nav',
                'navigation/settings' => 'navigation/base/settings',
            ]);
        });
    }

    private function _registerVariables(): void
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $event->sender->set('navigation', NavigationVariable::class);
        });
    }

    private function _registerCraftEventListeners(): void
    {
        // Allow elements to update our nodes
        Event::on(Elements::class, Elements::EVENT_BEFORE_SAVE_ELEMENT, [$this->getNodes(), 'onSaveElement']);
        Event::on(Elements::class, Elements::EVENT_AFTER_DELETE_ELEMENT, [$this->getNodes(), 'onDeleteElement']);

        // Prune deleted fields from nav
        Event::on(Fields::class, Fields::EVENT_AFTER_DELETE_FIELD, [$this->getNavs(), 'pruneDeletedField']);

        // Prune deleted sites from site settings
        Event::on(Sites::class, Sites::EVENT_AFTER_DELETE_SITE, [$this->getNavs(), 'pruneDeletedSite']);

        // Modify the element's HTML for the element index
        Event::on(Cp::class, Cp::EVENT_DEFINE_ELEMENT_INNER_HTML, [Node::class, 'getNodeElementTitleHtml']);
    }

    private function _registerProjectConfigEventListeners(): void
    {
        Craft::$app->getProjectConfig()->onAdd(Navs::CONFIG_NAV_KEY . '.{uid}', [$this->getNavs(), 'handleChangedNav'])
            ->onUpdate(Navs::CONFIG_NAV_KEY . '.{uid}', [$this->getNavs(), 'handleChangedNav'])
            ->onRemove(Navs::CONFIG_NAV_KEY . '.{uid}', [$this->getNavs(), 'handleDeletedNav']);

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
        });
    }

    private function _registerPermissions(): void
    {
        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $navs = $this->getNavs()->getAllNavs();

            $navPermissions = [];

            $navPermissions['navigation-createNavs'] = [
                'label' => Craft::t('navigation', 'Create navigations'),
            ];

            foreach ($navs as $nav) {
                $navPermissions['navigation-manageNav:' . $nav->uid] = [
                    'label' => Craft::t('navigation', 'Manage “{type}”', ['type' => $nav->name]),
                    'nested' => [
                        'navigation-editNav:' . $nav->uid => [
                            'label' => Craft::t('navigation', 'Edit navigation settings'),
                        ],
                        'navigation-deleteNav:' . $nav->uid => [
                            'label' => Craft::t('navigation', 'Delete navigation'),
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
        });

        Event::on(Gql::class, Gql::EVENT_REGISTER_GQL_QUERIES, function(RegisterGqlQueriesEvent $event) {
            $queries = NodeQuery::getQueries();

            foreach ($queries as $key => $value) {
                $event->queries[$key] = $value;
            }
        });

        Event::on(Gql::class, Gql::EVENT_REGISTER_GQL_SCHEMA_COMPONENTS, function(RegisterGqlSchemaComponentsEvent $event) {
            $navs = Navigation::$plugin->getNavs()->getAllNavs();

            if (!empty($navs)) {
                $label = Craft::t('navigation', 'Navigation');
                $event->queries[$label]['navigationNavs.all:read'] = ['label' => Craft::t('navigation', 'View all navigations')];

                foreach ($navs as $nav) {
                    $suffix = 'navigationNavs.' . $nav->uid;

                    $event->queries[$label][$suffix . ':read'] = [
                        'label' => Craft::t('navigation', 'View navigation - {nav}', ['nav' => Craft::t('site', $nav->name)]),
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

    private function _registerResaveCommand()
    {
        if (!Craft::$app instanceof ConsoleApplication) {
            return;
        }
        
        Event::on(ResaveController::class, ConsoleController::EVENT_DEFINE_ACTIONS, function(DefineConsoleActionsEvent $event) {
            $event->actions['navigation-nodes'] = [
                'action' => function(): int {
                    $controller = Craft::$app->controller;

                    $criteria = [];

                    if ($controller->navId !== null) {
                        $criteria['navId'] = explode(',', $controller->navId);
                    }

                    return $controller->saveElements(Node::class, $criteria);
                },
                'options' => ['navId'],
                'helpSummary' => 'Re-saves Navigation nodes.',
                'optionsHelp' => [
                    'type' => 'The nav ID of the nodes to resave.',
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
}
