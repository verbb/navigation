<?php
namespace verbb\navigation;

use verbb\navigation\base\PluginTrait;
use verbb\navigation\elements\Node;
use verbb\navigation\models\Settings;
use verbb\navigation\variables\NavigationVariable;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\services\Elements;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;
use yii\web\User;

class Navigation extends Plugin
{
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

        // Register elements
        Event::on(Elements::class, Elements::EVENT_REGISTER_ELEMENT_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Node::class;
        });

        // Register our CP routes
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, [$this, 'registerCpUrlRules']);

        // Setup Variables class (for backwards compatibility)
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $variable = $event->sender;
            $variable->set('navigation', NavigationVariable::class);
        });

        // Allow elements to update our nodes
        Event::on(Elements::class, Elements::EVENT_BEFORE_SAVE_ELEMENT, [ $this->nodes, 'onSaveElement' ]);
        Event::on(Elements::class, Elements::EVENT_AFTER_DELETE_ELEMENT, [ $this->nodes, 'onDeleteElement' ]);
    }

    public function getPluginName()
    {
        return Craft::t('navigation', $this->getSettings()->pluginName);
    }

    public function registerCpUrlRules(RegisterUrlRulesEvent $event)
    {
        $rules = [
            'navigation' => 'navigation/navs/index',
            'navigation/navs' => 'navigation/navs/index',
            'navigation/navs/new' => 'navigation/navs/edit-nav',
            'navigation/navs/edit/<navId:\d+>' => 'navigation/navs/edit-nav',
            'navigation/navs/build/<navId:\d+>' => 'navigation/navs/build-nav',
            'navigation/navs/build/<navId:\d+>/<siteHandle:{handle}>' => 'navigation/navs/build-nav',
            'navigation/settings' => 'navigation/base/settings',
        ];

        $event->rules = array_merge($event->rules, $rules);
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
}
