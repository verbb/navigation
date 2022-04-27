<?php
namespace verbb\navigation\integrations;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Json;

use craft\feedme\base\Element;
use craft\feedme\Plugin;

use Cake\Utility\Hash;

class NodeFeedMeElement extends Element
{
    // Properties
    // =========================================================================

    public static string $name = 'Navigation Node';
    public static string $class = Node::class;

    public ?ElementInterface $element = null;


    // Templates
    // =========================================================================

    public function getGroupsTemplate(): string
    {
        return 'navigation/_integrations/feed-me/groups';
    }

    public function getColumnTemplate(): string
    {
        return 'navigation/_integrations/feed-me/column';
    }

    public function getMappingTemplate(): string
    {
        return 'navigation/_integrations/feed-me/map';
    }


    // Public Methods
    // =========================================================================

    public function getGroups(): array
    {
        return Navigation::$plugin->getNavs()->getAllNavs();
    }

    public function getQuery($settings, array $params = []): mixed
    {
        $query = Node::find()
            ->status(null)
            ->navId($settings['elementGroup'][Node::class])
            ->siteId(Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);

        Craft::configure($query, $params);

        return $query;
    }

    public function setModel($settings): \craft\base\Element
    {
        $this->element = new Node();
        $this->element->navId = $settings['elementGroup'][Node::class];

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $this->element->siteId = $siteId;
        }

        return $this->element;
    }

    public function afterSave($data, $settings): void
    {
        $parent = Hash::get($data, 'parent');

        if ($parent && $parent !== $this->element->id) {
            $parentNode = Node::findOne(['id' => $parent]);

            Craft::$app->getStructures()->append($this->element->nav->structureId, $this->element, $parentNode);
        }
    }


    // Protected Methods
    // =========================================================================

    protected function parseParent($feedData, $fieldInfo): ?int
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        $match = Hash::get($fieldInfo, 'options.match');
        $create = Hash::get($fieldInfo, 'options.create');

        // Element lookups must have a value to match against
        if ($value === null || $value === '') {
            return null;
        }

        $query = Node::find()
            ->status(null)
            ->andWhere(['=', $match, $value]);

        if (isset($this->feed['siteId']) && $this->feed['siteId']) {
            $query->siteId($this->feed['siteId']);
        }

        $element = $query->one();

        if ($element) {
            return $element->id;
        }

        // Check if we should create the element. But only if title is provided (for the moment)
        if ($create && $match === 'title') {
            $element = new Node();
            $element->title = $value;
            $element->navId = $this->element->navId;

            if (!Craft::$app->getElements()->saveElement($element)) {
                Plugin::error('Navigation Node error: Could not create parent - `{e}`.', ['e' => Json::encode($element->getErrors())]);
            } else {
                Plugin::info('Navigation Node `#{id}` added.', ['id' => $element->id]);
            }

            return $element->id;
        }

        return null;
    }

}