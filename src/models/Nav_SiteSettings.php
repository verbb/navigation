<?php
namespace verbb\navigation\models;

use verbb\navigation\Navigation;

use Craft;
use craft\base\Model;
use craft\models\Site;
use craft\validators\SiteIdValidator;

use yii\base\InvalidConfigException;

class Nav_SiteSettings extends Model
{
    // Properties
    // =========================================================================

    public ?int $id = null;
    public ?int $navId = null;
    public ?int $siteId = null;
    public ?bool $enabled = null;

    private ?Nav $_nav = null;


    // Public Methods
    // =========================================================================

    public function getNav(): Nav
    {
        if (isset($this->_nav)) {
            return $this->_nav;
        }

        if (!$this->navId) {
            throw new InvalidConfigException('Node is missing its navigation ID');
        }

        if (($this->_nav = Navigation::$plugin->getNavs()->getNavById($this->navId)) === null) {
            throw new InvalidConfigException('Invalid navigation ID: ' . $this->navId);
        }

        return $this->_nav;
    }

    public function setNav(Nav $nav): void
    {
        $this->_nav = $nav;
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

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['id', 'navId', 'siteId'], 'number', 'integerOnly' => true];
        $rules[] = [['siteId'], SiteIdValidator::class];

        return $rules;
    }
}
