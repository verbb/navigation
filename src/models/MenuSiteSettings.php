<?php
namespace verbb\navigation\models;

use verbb\navigation\Navigation;
use verbb\navigation\models\MenuSettings;

use Craft;
use craft\base\Model;
use craft\models\Site;
use craft\validators\SiteIdValidator;

use yii\base\InvalidConfigException;

class MenuSiteSettings extends Model
{
    // Properties
    // =========================================================================

    public ?int $id = null;
    public ?int $menuId = null;
    public ?int $siteId = null;
    public ?bool $enabled = null;

    private ?MenuSettings $_menu = null;


    // Public Methods
    // =========================================================================

    public function getMenu(): MenuSettings
    {
        if (isset($this->_menu)) {
            return $this->_menu;
        }

        if (!$this->menuId) {
            throw new InvalidConfigException('Menu site settings model is missing its menu ID');
        }

        if (($this->_menu = Navigation::$plugin->getMenus()->getMenuById($this->menuId)) === null) {
            throw new InvalidConfigException('Invalid menu ID: ' . $this->menuId);
        }

        return $this->_menu;
    }

    public function setMenu(MenuSettings $menu): void
    {
        $this->_menu = $menu;
    }

    public function getSite(): Site
    {
        if (!$this->siteId) {
            throw new InvalidConfigException('Navigation site settings model is missing its site ID');
        }

        if (($site = Craft::$app->getSites()->getSiteById($this->siteId)) === null) {
            throw new InvalidConfigException('Invalid site ID: ' . $this->siteId);
        }

        return $site;
    }


    // Protected Methods
    // =========================================================================

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['id', 'menuId', 'siteId'], 'number', 'integerOnly' => true];
        $rules[] = [['siteId'], SiteIdValidator::class];

        return $rules;
    }
}
