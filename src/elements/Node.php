<?php
namespace verbb\navigation\elements;

use craft\behaviors\FieldLayoutBehavior;
use verbb\navigation\Navigation;
use verbb\navigation\elements\db\NodeQuery;
use verbb\navigation\events\NodeActiveEvent;
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
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use craft\helpers\UrlHelper;

use yii\base\Event;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\BaseHtml;

class Node extends Element
{
    // Constants
    // =========================================================================

    const EVENT_NODE_ACTIVE = 'modifyNodeActive';


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

    public static function gqlTypeNameByContext($context): string
    {
        return $context->handle . '_Node';
    }

    public static function gqlScopesByContext($context): array
    {
        return ['navs.' . $context->uid];
    }


    // Properties
    // =========================================================================

    public $id;
    public $elementId;
    public $siteId;
    public $navId;
    public $enabled = true;
    public $type;
    public $classes;
    public $urlSuffix;
    public $customAttributes = [];
    public $data = [];
    public $newWindow = false;

    public $newParentId;
    public $deletedWithNav = false;
    public $typeLabel = '';

    private $_url;
    private $_element;
    private $_nodeType;
    private $_elementUrl;
    private $_hasNewParent;
    private $_isActive;


    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        if (!empty($this->customAttributes)) {
            $this->customAttributes = Json::decode($this->customAttributes);
        }

        if (!empty($this->data)) {
            $this->data = Json::decode($this->data);
        }
    }

    public function getElement()
    {
        if ($this->_element !== null) {
            return $this->_element;
        }

        if (!$this->elementId) {
            return null;
        }

        return $this->_element = Craft::$app->getElements()->getElementById($this->elementId, $this->type, $this->getElementSiteId());
    }

    public function setElement($element = null)
    {
        $this->_element = $element;
    }

    public function getElementSiteId()
    {
        // Hijack the slug of the node element, because that's a 'free' column in the `elements_sites` table for the
        // node. Otherwise, we'd have to create a `node_sites` table, which I wasn't keen on at the time...
        // Pretty hacky though...
        if ($this->slug) {
            return (int)$this->slug;
        }

        return Craft::$app->getSites()->getCurrentSite()->id;
    }

    public function setElementSiteId($value)
    {
        $this->slug = $value;
    }

    public function getElementSlug()
    {
        if ($element = $this->getElement()) {
            return $element->slug;
        }

        return '';
    }

    public function getActive($includeChildren = true)
    {
        $isActive = $this->_getActive($includeChildren);

        // Allow plugins to modify this value
        $event = new NodeActiveEvent([
            'node' => $this,
            'isActive' => $isActive,
        ]);
        Event::trigger(static::class, self::EVENT_NODE_ACTIVE, $event);

        return $event->isActive;
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

    public function getRawUrl()
    {
        return $this->_url;
    }

    public function getUrl($includeSuffix = true)
    {
        $url = $this->getElementUrl() ?? $this->_url;

        if ($this->nodeType()) {
            return $this->nodeType()->getUrl();
        }

        $url = Craft::getAlias($url);

        // Allow twig support
        if ($url) {
            $object = $this->_getObject();
            $url = Craft::$app->getView()->renderObjectTemplate($url, $object);
        }

        if ($this->urlSuffix && $includeSuffix) {
            $url = $url . $this->urlSuffix;
        }

        return $url;
    }

    public function setUrl($value)
    {
        $this->_url = $value;
    }

    public function getElementUrl()
    {
        if ($this->_elementUrl !== null) {
            $path = ($this->_elementUrl === '__home__') ? '' : $this->_elementUrl;

            return UrlHelper::siteUrl($path, null, null, $this->getElementSiteId());
        } else {
            $element = $this->getElement();

            if ($element) {
                return $element->url;
            }
        }

        return null;
    }

    public function setElementUrl($value)
    {
        $this->_elementUrl = $value;
    }

    public function getLinkAttributes($extraAttributes = null)
    {
        $object = $this->_getObject();

        $classes = $this->classes ?
            Craft::$app->view->renderObjectTemplate($this->classes, $object) : null;

        $attributes = [
            'href' => $this->getUrl(),
            'target' => $this->newWindow ? '_blank' : null,
            'rel' => $this->newWindow ? 'noopener' : null,
            'class' => [ $classes ],
        ];

        if (is_array($this->customAttributes)) {
            foreach ($this->customAttributes as $attribute) {
                $key = $attribute['attribute'];
                $val = $attribute['value'];

                $attributes[$key] = Craft::$app->view->renderObjectTemplate($val, $object);
            }
        }

        if (is_array($extraAttributes)) {
            $attributes = ArrayHelper::merge($attributes, $extraAttributes);
        }

        return Template::raw(BaseHtml::renderTagAttributes($attributes));
    }

    public function getLink($attributes = null)
    {
        return Template::raw('<a ' . $this->getLinkAttributes($attributes) . '>' . Html::encode($this->__toString()) . '</a>');
    }

    public function getTarget()
    {
        return $this->newWindow ? '_blank' : '';
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

    public function nodeType()
    {
        if ($this->_nodeType != null) {
            // If a custom node type, be sure to send through this element
            $this->_nodeType->node = $this;

            return $this->_nodeType;
        }

        $registeredNodeTypes = Navigation::$plugin->getNodeTypes()->getRegisteredNodeTypes();

        foreach ($registeredNodeTypes as $registeredNodeType) {
            if ($this->type === get_class($registeredNodeType)) {
                $registeredNodeType->node = $this;

                return $this->_nodeType = $registeredNodeType;
            }
        }

        return null;
    }

    public function getNodeTypeLabel()
    {
        if ($this->getElement()) {
            $displayName = StringHelper::toLowerCase($this->getElement()->displayName());

            return $displayName;
        }

        if ($this->isManual()) {
            return Craft::t('navigation', 'manual');
        }

        if ($this->nodeType()) {
            return StringHelper::toLowerCase($this->nodeType()->displayName());
        }

        return '';
    }

    public function isElement()
    {
        $registeredElements = Navigation::$plugin->getElements()->getRegisteredElements();

        foreach ($registeredElements as $registeredElement) {
            if ($this->type == $registeredElement['type']) {
                return true;
            }
        }

        return false;
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

    public function getSupportedSites(): array
    {
        $nav = $this->getNav();

        if (!$nav->propagateNodes) {
            return [$this->siteId];
        }

        return Craft::$app->getSites()->getAllSiteIds();
    }

    public function getGqlTypeName(): string
    {
        return static::gqlTypeNameByContext($this->getNav());
    }


    // Events
    // -------------------------------------------------------------------------

    public function beforeSave(bool $isNew): bool
    {
        // Set the structure ID for Element::attributes() and afterSave()
        $this->structureId = $this->getNav()->structureId;

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

        // If this is propagating, we want to fetch the information for that site's linked element
        if ($this->propagating && $this->isElement()) {
            $localeElement = Craft::$app->getElements()->getElementById($this->elementId, null, $this->siteId);

            if ($localeElement) {
                $this->elementSiteId = $localeElement->siteId;

                // Only update the title if we haven't overridden it
                if (!$this->hasOverriddenTitle()) {
                    $this->title = $localeElement->title;
                }
            }
        }

        // If no title is set (for a custom node type for instance), generate one.
        if (!$this->title && $this->nodeType()) {
            $this->title = $this->nodeType()->displayName();
        }

        // Save the linked element's site id to the slug - again, our hacky way...
        if ($this->elementSiteId) {
            $this->slug = $this->elementSiteId;
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
        $record->navId = $this->navId;
        $record->url = $this->getRawUrl();
        $record->type = $this->type;
        $record->classes = $this->classes;
        $record->urlSuffix = $this->urlSuffix;
        $record->customAttributes = $this->customAttributes;
        $record->data = $this->data;
        $record->newWindow = $this->newWindow;

        // Don't store the URL if its an element. We should rely on its element URL.
        // Check for custom types, they might want to save the URL
        if ($this->type && !$this->nodeType()) {
            $record->url = null;
        }

        $record->save(false);

        $this->id = $record->id;
        $this->typeLabel = $this->getNodeTypeLabel();

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

    public function beforeDelete(): bool
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // Update the node record
        $data = [
            'deletedWithNav' => $this->deletedWithNav,
            'parentId' => null,
        ];

        if ($this->structureId) {
            // Remember the parent ID, in case the entry needs to be restored later
            $parentId = $this->getAncestors(1)
                ->anyStatus()
                ->select(['elements.id'])
                ->scalar();

            if ($parentId) {
                $data['parentId'] = $parentId;
            }
        }

        Craft::$app->getDb()->createCommand()
            ->update('{{%navigation_nodes}}', $data, ['id' => $this->id], [], false)
            ->execute();

        return true;
    }

    public function afterRestore()
    {
        $structureId = $this->getNav()->structureId;

        // Add the node back into its structure
        $parent = self::find()
            ->structureId($structureId)
            ->innerJoin('{{%navigation_nodes}} j', '[[j.parentId]] = [[elements.id]]')
            ->andWhere(['j.id' => $this->id])
            ->one();

        if (!$parent) {
            Craft::$app->getStructures()->appendToRoot($structureId, $this);
        } else {
            Craft::$app->getStructures()->append($structureId, $this, $parent);
        }

        parent::afterRestore();
    }

    public function getFieldLayout()
    {
        $nav = $this->navId === null ? null : $this->getNav();

        return $nav ? $nav->getNavFieldLayout() : null;
    }


    // Private Methods
    // =========================================================================

    public function _getActive($includeChildren = true)
    {
        if ($this->_isActive && $includeChildren) {
            return true;
        }

        $request = Craft::$app->getRequest();

        // Don't run the for console requests. This is called when populating the Node element
        if ($request->getIsConsoleRequest()) {
            return;
        }

        $relativeUrl = str_replace(UrlHelper::siteUrl(), '', $this->getUrl(false));
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
            if (substr($currentUrl, 0, strlen($relativeUrl . '/')) === $relativeUrl . '/') {
                if ($relativeUrl !== '') {
                    $isActive = true;
                }
            }
        }

        return $isActive;
    }

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
        $oldParentQuery->siteId($this->siteId);
        $oldParentQuery->anyStatus();
        $oldParentQuery->select('elements.id');
        $oldParentId = $oldParentQuery->scalar();

        return $this->newParentId != $oldParentId;
    }

    private function _getObject()
    {
        return [
            'currentUser' => Craft::$app->getUser()->getIdentity(),
        ];
    }
}
