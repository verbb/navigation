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

                if (!Navigation::$plugin->navs->saveNav($nav)) {
                    echo "    > ERROR: Unable to migrate nav `{$NaveeNav['handle']}` ...\n";

                    Craft::dump($nav->getErrors());

                    continue;
                }

                foreach (Craft::$app->getSites()->getAllSites() as $site) {
                    $NaveeNodes = (new Query())
                        ->select(['*'])
                        ->from(['{{%navee_nodes}} nodes'])
                        ->leftJoin('{{%elements_sites}} elements_sites', '[[elements_sites.elementId]] = [[nodes.id]]')
                        ->where(['navigationId' => $NaveeNav['id'], 'siteId' => $site['id']])
                        ->all();

                    $processedNodes = [];

                    foreach ($NaveeNodes as $key => $NaveeNode) {
                        try {
                            $node = new Node();

                            $NaveeElement = (new Query())
                                ->select(['*'])
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
                                $parentStructureElement = (new Query())
                                    ->select(['*'])
                                    ->from(['{{%structureelements}} structureelements'])
                                    ->where(['structureelements.lft' => $structureElement['lft'] - 1, 'structureelements.rgt' => $structureElement['rgt'] + 1])
                                    ->one();

                                if ($parentStructureElement) {
                                    // Get the new node for the already processed parent
                                    $parentNodeId = $processedNodes[$parentStructureElement['elementId']] ?? null;

                                    if ($parentNodeId) {
                                        $node = Navigation::$plugin->nodes->getNodeById($newNodeId);
                                        $node->newParentId = $parentNodeId;

                                        if (!Craft::$app->getElements()->saveElement($node, true, $this->propagate)) {
                                            echo "    > ERROR: Unable to re-save node `{$node['title']}` ...\n";

                                            Craft::dump($node->getErrors());
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

        } catch (\Throwable $e) {
            Craft::dump($e->getMessage());
        }

        return true;
    }

    public function safeDown()
    {
        return false;
    }
}
