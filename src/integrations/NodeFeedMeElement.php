<?php
namespace verbb\navigation\integrations;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Json;

use craft\feedme\Plugin;
use craft\feedme\base\Element;
use craft\feedme\events\FeedProcessEvent;
use craft\feedme\services\Process;

use Cake\Utility\Hash;

use yii\base\Event;

class NodeFeedMeElement extends Element
{
    // Properties
    // =========================================================================

    public static string $name = 'Navigation Node';
    public static string $class = Node::class;

    public $element = null;


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

    public function init(): void
    {
        parent::init();

        Event::on(Process::class, Process::EVENT_STEP_AFTER_ELEMENT_SAVE, function(FeedProcessEvent $event): void {
            if ($event->feed['elementType'] === Node::class) {
                $this->_processNestedNode($event);
            }
        });
    }

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


    // Protected Methods
    // =========================================================================

    protected function parseParentId($feedData, $fieldInfo): ?int
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        // In Craft 4, we need to explicitly call `setParentId()`, as it's no longer a property
        // only available as a setter method.
        $this->element->setParentId($value);

        return $value;
    }

    protected function parseElementId($feedData, $fieldInfo): ?int
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $match = Hash::get($fieldInfo, 'options.match');

        // Element lookups must have a value to match against
        if ($value === null || $value === '') {
            return null;
        }

        $elementId = null;

        // Because we can match on element attributes and custom fields, AND we're directly using SQL
        // queries in our `where` below, we need to check if we need a prefix for custom fields accessing
        // the content table.
        $columnName = $match;

        if (Craft::$app->getFields()->getFieldByHandle($match)) {
            $columnName = Craft::$app->getFields()->oldFieldColumnPrefix . $match;
        }

        $result = (new Query())
            ->select(['elements.id', 'elements_sites.elementId'])
            ->from(['{{%elements}} elements'])
            ->innerJoin('{{%elements_sites}} elements_sites', '[[elements_sites.elementId]] = [[elements.id]]')
            ->innerJoin('{{%content}} content', '[[content.elementId]] = [[elements.id]]')
            ->where(['=', $columnName, $value])
            ->andWhere(['dateDeleted' => null])
            ->one();

        if ($result) {
            $elementId = $result['id'];
        }

        if ($elementId) {
            return $elementId;
        }

        return null;
    }


    // Private Methods
    // =========================================================================

    private function _processNestedNode($event): void
    {
        // Save the imported node as the parent, we'll need it in a sec
        $parentId = $event->element->id;

        // Check if we're mapping a node to start looking for children.
        $childrenNode = Hash::get($event->feed, 'fieldMapping.children.node');

        if (!$childrenNode) {
            return;
        }

        // Check if there's any children data for the node we've just imported
        $expandedData = Hash::expand($event->feedData, '/');
        $childrenData = Hash::get($expandedData, $childrenNode, []);

        foreach ($childrenData as $childData) {
            // Prep the data, cutting the nested content to the top of the array
            $newFeedData = Hash::flatten($childData, '/');

            $processedElementIds = [];

            // Directly modify the field mapping data, because we're programatically adding
            // the `parentId`, which cannot be mapped.
            $event->feed['fieldMapping']['parentId'] = [
                'attribute' => true,
                'default' => $parentId,
            ];

            // Trigger the import for each child
            Plugin::$plugin->getProcess()->processFeed(-1, $event->feed, $processedElementIds, $newFeedData);
        }
    }

}