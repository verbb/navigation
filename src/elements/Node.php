<?php
namespace verbb\navigation\elements;

use verbb\navigation\Navigation;
use verbb\navigation\elements\db\NodeQuery;
use verbb\navigation\models\Nav as NavModel;
use verbb\navigation\records\Nav as NavRecord;
use verbb\navigation\records\Node as NodeRecord;

use Craft;
use craft\base\Element;
use craft\controllers\ElementIndexesController;
use craft\db\Query;
use craft\elements\actions\Delete;
use craft\elements\actions\Edit;
use craft\elements\actions\NewChild;
use craft\elements\actions\SetStatus;
use craft\elements\actions\View;
use craft\elements\db\ElementQueryInterface;
use Craft\helpers\ArrayHelper;
use craft\helpers\Html;
use craft\helpers\Template;
use craft\helpers\UrlHelper;

use yii\base\Exception;
use yii\base\InvalidConfigException;

class Node extends Element
{
    // Static
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('navigation', 'Navigation Node');
    }

    public static function refHandle()
    {
        return 'node';
    }

    public static function hasContent(): bool
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

    public static function find(): ElementQueryInterface
    {
        return new NodeQuery(static::class);
    }

    // Properties
    // =========================================================================

    public $id;
    public $elementId;
    public $elementSiteId;
    public $siteId;
    public $navId;
    public $enabled = true;
    public $type;
    public $classes;
    public $newWindow = false;

    public $newParentId;

    private $_url;
    private $_element;
    private $_elementUrl;
    private $_hasNewParent;
    private $_isActive;


    // Public Methods
    // =========================================================================

    public function getElement()
    {
        if ($this->_element !== null) {
            return $this->_element;
        }

        if ($this->elementId === null) {
            return null;
        }

        return $this->_element = Craft::$app->getElements()->getElementById($this->elementId, $this->type, $this->elementSiteId);
    }

    public function setElement($element = null)
    {
        $this->_element = $element;
    }

    public function getActive($includeChildren = true)
    {
        if ($this->_isActive) {
            return true;
        }

        $request = Craft::$app->getRequest();

        // Don't run the for console requests. This is called when populating the Node element
        if ($request->getIsConsoleRequest()) {
            return;
        }

        $relativeUrl = str_replace(UrlHelper::siteUrl(), '', $this->getUrl());
        $currentUrl = implode('/', $request->getSegments());

        // Stop straight away if this is potentially the homepage
        if ($currentUrl === '') {
            // Check if we have the homepage entry in the nav, and mark that as active
            if ($this->_elementUrl && $this->_elementUrl === '__home__') {
                return true;
            }

            return false;
        }

        // If addTrailingSlashesToUrls, remove trailing '/' for comparison
        if (Craft::$app->config->general->addTrailingSlashesToUrls) {
            $relativeUrl = rtrim($relativeUrl, '/');
        }

        // If manual URL, make sure to remove a leading '/' for comparison
        if ($this->isManual()) {
            $relativeUrl = ltrim($relativeUrl, '/');
        }

        $isActive = (bool)($currentUrl === $relativeUrl);

        // Also check if any children are active
        if ($includeChildren) {
            // Then, provide a helper based purely on the URL structure.
            // /example-page and /example-page/nested-page should both be active, even if both aren't nodes.
            if (substr($currentUrl, 0, strlen($relativeUrl)) === $relativeUrl) {
                if ($relativeUrl !== '') {
                    $isActive = true;
                }
            }
        }
        
        return $isActive;
    }

    public function setIsActive($value)
    {
        $this->_isActive = $value;
    }

    public function hasActiveChild()
    {
        if ($this->hasDescendants) {
            $descendants = $this->descendants->all();

            foreach ($descendants as $key => $descendant) {
                if ($descendant->getActive()) {
                    $this->setIsActive(true);

                    return $this->getActive();
                }
            }
        }
    }

    public function getUrl()
    {
        return $this->getElementUrl() ?? $this->_url;
    }

    public function setUrl($value)
    {
        $this->_url = $value;
    }

    public function getElementUrl()
    {
        if ($this->_elementUrl !== null) {
            $path = ($this->_elementUrl === '__home__') ? '' : $this->_elementUrl;

            return UrlHelper::siteUrl($path, null, null, $this->elementSiteId);
        }

        return null;
    }

    public function setElementUrl($value)
    {
        $this->_elementUrl = $value;
    }

    public function getLink()
    {
        $newWindow = '';
        $classes = '';

        if ($this->newWindow) {
            $newWindow = 'target="_blank" rel="noopener"';
        }

        if ($this->classes) {
            $classes = 'class="' . $this->classes . '"';
        }

        return Template::raw('<a href="' . $this->getUrl() . '" ' . $newWindow . ' ' . $classes . '>' . Html::encode($this->__toString()) . '</a>');
    }

    public function getNav()
    {
        if ($this->navId === null) {
            throw new InvalidConfigException('Node is missing its navigation ID');
        }

        $nav = Navigation::$plugin->navs->getNavById($this->navId);

        if (!$nav) {
            throw new InvalidConfigException('Invalid navigation ID: ' . $this->navId);
        }

        return $nav;
    }

    public function isManual()
    {
        return (bool)!$this->type;
    }

    public function hasOverriddenTitle()
    {
        $element = $this->getElement();

        if ($element) {
            if ($element->title !== $this->title) {
                return true;
            }
        }

        return false;
    }


    // Events
    // -------------------------------------------------------------------------

    public function beforeSave(bool $isNew): bool
    {
        if ($this->_hasNewParent()) {
            if ($this->newParentId) {
                $parentNode = Navigation::$plugin->nodes->getNodeById($this->newParentId, $this->siteId);

                if (!$parentNode) {
                    throw new Exception('Invalid node ID: ' . $this->newParentId);
                }
            } else {
                $parentNode = null;
            }

            $this->setParent($parentNode);
        }

        return parent::beforeSave($isNew);
    }

    public function afterSave(bool $isNew)
    {
        // Get the node record
        if (!$isNew) {
            $record = NodeRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid node ID: ' . $this->id);
            }
        } else {
            $record = new NodeRecord();
            $record->id = $this->id;
        }

        $record->elementId = $this->elementId;
        $record->elementSiteId = $this->elementSiteId;
        $record->navId = $this->navId;
        $record->url = $this->url;
        $record->type = $this->type;
        $record->classes = $this->classes;
        $record->newWindow = $this->newWindow;

        // Don't store the URL if its an element. We should rely on its element URL.
        if ($this->type) {
            $record->url = null;
        }

        $record->save(false);

        $this->id = $record->id;

        $nav = $this->getNav();

        // Has the parent changed?
        if ($this->_hasNewParent()) {
            if (!$this->newParentId) {
                Craft::$app->getStructures()->appendToRoot($nav->structureId, $this);
            } else {
                Craft::$app->getStructures()->append($nav->structureId, $this, $this->getParent());
            }
        }

        parent::afterSave($isNew);
    }


    // Private Methods
    // =========================================================================

    private function _hasNewParent(): bool
    {
        if ($this->_hasNewParent !== null) {
            return $this->_hasNewParent;
        }

        return $this->_hasNewParent = $this->_checkForNewParent();
    }

    private function _checkForNewParent(): bool
    {
        // Is it a brand new node?
        if ($this->id === null) {
            return true;
        }

        // Was a new parent ID actually submitted?
        if ($this->newParentId === null) {
            return false;
        }

        // Is it set to the top level now, but it hadn't been before?
        if (!$this->newParentId && $this->level != 1) {
            return true;
        }

        // Is it set to be under a parent now, but didn't have one before?
        if ($this->newParentId && $this->level == 1) {
            return true;
        }

        // Is the newParentId set to a different node ID than its previous parent?
        $oldParentQuery = self::find();
        $oldParentQuery->ancestorOf($this);
        $oldParentQuery->ancestorDist(1);
        $oldParentQuery->status(null);
        $oldParentQuery->siteId($this->siteId);
        $oldParentQuery->enabledForSite(false);
        $oldParentQuery->select('elements.id');
        $oldParentId = $oldParentQuery->scalar();

        return $this->newParentId != $oldParentId;
    }
}
