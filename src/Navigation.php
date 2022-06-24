<?php
namespace verbb\navigation;

use verbb\navigation\base\PluginTrait;
use verbb\navigation\elements\Node;
use verbb\navigation\fields\NavigationField;
use verbb\navigation\gql\interfaces\NodeInterface;
use verbb\navigation\gql\queries\NodeQuery;
use verbb\navigation\helpers\Gql as GqlHelper;
use verbb\navigation\integrations\NodeFeedMeElement;
use verbb\navigation\models\Settings;
use verbb\navigation\services\Navs;
use verbb\navigation\twigextensions\Extension;
use verbb\navigation\variables\NavigationVariable;

use Craft;
use craft\base\Plugin;
use craft\console\controllers\ResaveController;
use craft\console\Controller as ConsoleController;
use craft\events\ConfigEvent;
use craft\events\DefineConsoleActionsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterGqlQueriesEvent;
use craft\events\RegisterGqlSchemaComponentsEvent;
use craft\events\RegisterGqlTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Gql;
use craft\services\ProjectConfig;
use craft\services\Sites;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;
use yii\web\User;

use craft\feedme\events\RegisterFeedMeElementsEvent;
use craft\feedme\services\Elements as FeedMeElements;

use craft\gatsbyhelper\events\RegisterSourceNodeTypesEvent;
use craft\gatsbyhelper\services\SourceNodes;

class Navigation extends Plugin
{
    // Public Properties
    // =========================================================================

    public $schemaVersion = '1.0.21';
    public $hasCpSettings = true;
    public $hasCpSection = true;


    // Traits
    // =========================================================================

    use PluginTrait;


    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->_setPluginComponents();
        $this->_setLogging();
        $this->_registerCpRoutes();
        $this->_registerVariables();
        $this->_registerCraftEventListeners();
        $this->_registerProjectConfigEventListeners();
        $this->_registerTwigExtensions();
        $this->_registerFieldTypes();
        $this->_registerElementTypes();
        $this->_registerPermissions();
        $this->_registerGraphQl();
        $this->_registerFeedMeSupport();
        $this->_defineResaveCommand();
    }

    public function getPluginName()
    {
        return Craft::t('navigation', $this->getSettings()->pluginName);
    }

    public function getSettingsResponse()
    {
        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('navigation/settings'));
    }

    public function getCpNavItem()
    {
        $navItem = parent::getCpNavItem();
        $navItem['label'] = $this->getPluginName();

        return $navItem;
    }


    // Protected Methods
    // =========================================================================

    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }


    // Private Methods
    // =========================================================================

    private function _registerTwigExtensions()
    {
        Craft::$app->getView()->registerTwigExtension(new Extension);
    }
    
    private function _registerCpRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'navigation' => 'navigation/navs/index',
                'navigation/navs' => 'navigation/navs/index',
                'navigation/navs/new' => 'navigation/navs/edit-nav',
                'navigation/navs/edit/<navId:\d+>' => 'navigation/navs/edit-nav',
                'navigation/navs/build/<navId:\d+>' => 'navigation/navs/build-nav',
                'navigation/navs/build/<navId:\d+>/<siteHandle:{handle}>' => 'navigation/navs/build-nav',
                'navigation/settings' => 'navigation/base/settings',
            ]);
        });
    }

    private function _registerVariables()
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $event->sender->set('navigation', NavigationVariable::class);
        });
    }

    private function _registerCraftEventListeners()
    {
        // Allow elements to update our nodes
        Event::on(Elements::class, Elements::EVENT_BEFORE_SAVE_ELEMENT, [$this->getNodes(), 'onSaveElement']);
        Event::on(Elements::class, Elements::EVENT_AFTER_DELETE_ELEMENT, [$this->getNodes(), 'onDeleteElement']);
    }

    private function _registerProjectConfigEventListeners()
    {
        Craft::$app->getProjectConfig()->onAdd(Navs::CONFIG_NAV_KEY . '.{uid}', [$this->getNavs(), 'handleChangedNav'])
            ->onUpdate(Navs::CONFIG_NAV_KEY . '.{uid}', [$this->getNavs(), 'handleChangedNav'])
            ->onRemove(Navs::CONFIG_NAV_KEY . '.{uid}', [$this->getNavs(), 'handleDeletedNav']);
    }

    private function _registerFieldTypes()
    {
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = NavigationField::class;
        });
    }

    private function _registerElementTypes()
    {
        Event::on(Elements::class, Elements::EVENT_REGISTER_ELEMENT_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Node::class;
        });
    }

    private function _registerPermissions()
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
                            'label' => Craft::t('navigation', 'Delete navigation')
                        ],
                    ],
                ];
            }

            $event->permissions[Craft::t('navigation', 'Navigation')] = $navPermissions;
        });
    }

    private function _registerGraphQl()
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

        if (version_compare(Craft::$app->getInfo()->version, '3.5.0', '>=')) {
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
        }

        if (class_exists(SourceNodes::class)) {
            Event::on(SourceNodes::class, SourceNodes::EVENT_REGISTER_SOURCE_NODE_TYPES, function(RegisterSourceNodeTypesEvent $event) {
                if (GqlHelper::canQueryNavigation()) {
                    $event->types[NodeInterface::getName()] = [
                        'node' => 'node',
                        'list' => 'nodes',
                        'filterArgument' => '',
                        'filterTypeExpression' => '(.+)_Node',
                        'targetInterface' => NodeInterface::getName(),
                    ];
                }
            });
        }
    }

    private function _registerFeedMeSupport()
    {
        if (class_exists(FeedMeElements::class)) {
            Event::on(FeedMeElements::class, FeedMeElements::EVENT_REGISTER_FEED_ME_ELEMENTS, function(RegisterFeedMeElementsEvent $e) {
                $e->elements[] = NodeFeedMeElement::class;
            });
        }
    }

    private function _defineResaveCommand()
    {
        if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        Event::on(ResaveController::class, ConsoleController::EVENT_DEFINE_ACTIONS, function(DefineConsoleActionsEvent $event) {
            $event->actions['navigation-nodes'] = [
                'action' => function(): int {
                    $controller = Craft::$app->controller;

                    $query = Node::find();

                    if ($controller->navId !== null) {
                        $query->navId(explode(',', $controller->navId));
                    }

                    return $controller->saveElements($query);
                },
                'options' => ['navId'],
                'helpSummary' => 'Re-saves Navigation nodes.',
                'optionsHelp' => [
                    'type' => 'The nav ID of the nodes to resave.',
                ],
            ];
        });
    }
}
