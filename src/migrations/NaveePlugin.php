<?php
namespace verbb\navigation\migrations;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\models\Nav as NavModel;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

class NaveePlugin extends Migration
{
    public $propagate = true;
    public $assignToDefaultSite = false;


    // Public Methods
    // =========================================================================

    public function safeUp()
    {
        try {
            if (!$this->db->tableExists('{{%navee_navigations}}')) {
                return true;
            }

            $NaveeNavs = (new Query())
                ->select(['*'])
                ->from(['{{%navee_navigations}} navigations'])
                ->all();

            $siteSettings = [];

            foreach (Craft::$app->getSites()->getAllSites() as $site) {
                $siteSettings[$site->uid]['enabled'] = true;
            }

            foreach ($NaveeNavs as $key => $NaveeNav) {
                $nav = Navigation::$plugin->navs->getNavByHandle($NaveeNav['handle']);

                echo "\n    > Migrating nav `{$NaveeNav['handle']}` ...\n";

                if (!$nav) {
                    $nav = new NavModel();
                }

                $nav->name = $NaveeNav['name'];
                $nav->handle = $NaveeNav['handle'];
                $nav->maxLevels = $NaveeNav['maxLevels'];
                $nav->structureId = $NaveeNav['structureId'];
                $nav->siteSettings = $siteSettings;

                if (!Navigation::$plugin->navs->saveNav($nav)) {
                    echo "    > ERROR: Unable to migrate nav `{$NaveeNav['handle']}` ...\n";

                    Craft::dump($nav->getErrors());

                    continue;
                }

                foreach (Craft::$app->getSites()->getAllSites() as $site) {
                    $NaveeNodes = (new Query())
                        ->select(['nodes.id', 'elementId', 'customUri', 'class', 'target', 'entryId', 'categoryId', 'assetId'])
                        ->from(['{{%navee_nodes}} nodes'])
                        ->leftJoin('{{%elements_sites}} elements_sites', '[[elements_sites.elementId]] = [[nodes.id]]')
                        ->where(['navigationId' => $NaveeNav['id'], 'siteId' => $site['id']])
                        ->all();

                    $processedNodes = [];

                    foreach ($NaveeNodes as $key => $NaveeNode) {
                        try {
                            $node = new Node();

                            $NaveeElement = (new Query())
                                ->select(['elements.id', 'title', 'enabled'])
                                ->from(['{{%elements}} elements'])
                                ->leftJoin('{{%content}} content', '[[content.elementId]] = [[elements.id]]')
                                ->where(['elements.id' => $NaveeNode['elementId'], 'type' => 'Navee_Node', 'siteId' => $site['id']])
                                ->one();

                            if (!$NaveeElement) {
                                echo "    > ERROR: Unable to find element for node `{$NaveeNode['id']}` ...\n";

                                continue;
                            }

                            $node->title = $NaveeElement['title'];
                            $node->enabled = $NaveeElement['enabled'];
                            $node->siteId = $site->id;
                            $node->navId = $nav->id;
                            $node->url = $NaveeNode['customUri'];
                            $node->classes = $NaveeNode['class'];

                            if ($NaveeNode['target'] === '_blank') {
                                $node->newWindow = true;
                            }

                            if ($NaveeNode['entryId']) {
                                $node->type = \craft\elements\Entry::class;
                                $node->elementId = $NaveeNode['entryId'];
                            } else if ($NaveeNode['categoryId']) {
                                $node->type = \craft\elements\Category::class;
                                $node->elementId = $NaveeNode['categoryId'];
                            } else if ($NaveeNode['assetId']) {
                                $node->type = \craft\elements\Asset::class;
                                $node->elementId = $NaveeNode['assetId'];
                            }

                            if (Craft::$app->getElements()->saveElement($node, true, $this->propagate)) {
                                echo "    > Migrated node `{$NaveeNode['id']}` ...\n";

                                $processedNodes[$NaveeNode['id']] = $node->id;
                            } else {
                                echo "    > ERROR: Unable to save node `{$NaveeNode['id']}` ...\n";

                                Craft::dump($node->getErrors());
                            }
                        } catch (\Throwable $e) {
                            echo "    > ERROR: Unable to save node `{$NaveeNode['id']}` ...\n";

                            Craft::dump($e->getMessage());

                            echo "    > `{$this->getExceptionTraceAsString($e)}`";

                            continue;
                        }
                    }

                    // Now the nodes are in, setup any hierarchy
                    foreach ($processedNodes as $oldNodeId => $newNodeId) {
                        // Get Structure information for the old node
                        $structureElement = (new Query())
                            ->select(['*'])
                            ->from(['{{%structureelements}} structureelements'])
                            ->where(['structureelements.elementId' => $oldNodeId])
                            ->one();

                        if ($structureElement) {
                            $level = $structureElement['level'] ?? null;

                            if ((int)$level > 1) {
                                // Find the parent structure
                                $parentStructureElementQuery = (new Query())
                                    ->select(['*'])
                                    ->from(['{{%structureelements}} structureelements'])
                                    ->where(['structureelements.structureId' => $structureElement['structureId']])
                                    ->andWhere('structureelements.lft < :lft', ['lft' => $structureElement['lft']])
                                    ->andWhere('structureelements.rgt > :rgt', ['rgt' => $structureElement['rgt']])
                                    ->orderBy('`structureelements`.`rgt` - `structureelements`.`lft` ASC');

                                $parentStructureElement = $parentStructureElementQuery->one();

                                if ($parentStructureElement) {
                                    // Get the new node for the already processed parent
                                    $parentNodeId = $processedNodes[$parentStructureElement['elementId']] ?? null;

                                    if ($parentNodeId) {
                                        $node = Navigation::$plugin->nodes->getNodeById($newNodeId);

                                        if ($node) {
                                            $node->newParentId = $parentNodeId;

                                            if (!Craft::$app->getElements()->saveElement($node, true, $this->propagate)) {
                                                echo "    > ERROR: Unable to re-save node `{$node['title']}` ...\n";

                                                Craft::dump($node->getErrors());
                                            }
                                        }
                                    }
                                } else {
                                    echo "    > WARNING: Unable to find parent for `{$node['title']}` ...\n";

                                    echo $parentStructureElementQuery->getRawSql();
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Craft::dump($e->getMessage());

            echo "    > `{$this->getExceptionTraceAsString($e)}`";
        }

        return true;
    }

    public function safeDown()
    {
        return false;
    }

    private function getExceptionTraceAsString($exception)
    {
        $rtn = "";
        $count = 0;

        foreach ($exception->getTrace() as $frame) {
            $args = "";

            if (isset($frame['args'])) {
                $args = [];

                foreach ($frame['args'] as $arg) {
                    if (is_string($arg)) {
                        $args[] = "'" . $arg . "'";
                    } else if (is_array($arg)) {
                        $args[] = "Array";
                    } else if (is_null($arg)) {
                        $args[] = 'NULL';
                    } else if (is_bool($arg)) {
                        $args[] = ($arg) ? "true" : "false";
                    } else if (is_object($arg)) {
                        $args[] = get_class($arg);
                    } else if (is_resource($arg)) {
                        $args[] = get_resource_type($arg);
                    } else {
                        $args[] = $arg;
                    }
                }

                $args = join(", ", $args);
            }

            $rtn .= sprintf("#%s %s(%s): %s(%s)\n",
                $count,
                isset($frame['file']) ? $frame['file'] : '[internal function]',
                isset($frame['line']) ? $frame['line'] : '',
                (isset($frame['class'])) ? $frame['class'] . $frame['type'] . $frame['function'] : $frame['function'],
                $args);

            $count++;
        }

        return $rtn;
    }
}
