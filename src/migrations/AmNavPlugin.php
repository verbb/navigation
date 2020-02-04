<?php
namespace verbb\navigation\migrations;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\models\Nav as NavModel;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

class AmNavPlugin extends Migration
{
    public $propagate = true;
    public $assignToDefaultSite = false;

    private $processedNodes = [];

    // Public Methods
    // =========================================================================

    public function safeUp()
    {
        try {
            if (!$this->db->tableExists('{{%amnav_navs}}')) {
                return true;
            }

            $sitesByLanguage = [];

            foreach (Craft::$app->getSites()->getAllSites() as $site) {
                $languageHandle = strtolower(str_replace('-', '_', $site->language));

                $sitesByLanguage[$languageHandle] = $site;
            }

            $defaultSite = Craft::$app->getSites()->getCurrentSite();

            $AmNavs = (new Query())
                ->select(['*'])
                ->from(['{{%amnav_navs}}'])
                ->all();

            foreach ($AmNavs as $key => $AmNav) {
                $nav = Navigation::$plugin->navs->getNavByHandle($AmNav['handle']);

                echo "\n    > Migrating nav `{$AmNav['handle']}` ...\n";

                if (!$nav) {
                    $nav = new NavModel();
                }

                $nav->name = $AmNav['name'];
                $nav->handle = $AmNav['handle'];

                $settings = Json::decode($AmNav['settings']);
                $nav->maxLevels = $settings['maxLevels'] ?? '';
                $nav->sortOrder = $key;

                if (!Navigation::$plugin->navs->saveNav($nav)) {
                    echo "    > ERROR: Unable to migrate nav `{$AmNav['handle']}` ...\n";

                    Craft::dump($nav->getErrors());

                    continue;
                }

                $AmNodes = (new Query())
                    ->select(['*'])
                    ->from(['{{%amnav_nodes}} nodes'])
                    ->where(['navId' => $AmNav['id']])
                    ->orderBy('parentId ASC, order ASC')
                    ->all();

                foreach ($AmNodes as $key => $AmNode) {
                    try {
                        $node = new Node();

                        $node->title = $AmNode['name'];
                        $node->enabled = $AmNode['enabled'];
                        $node->elementId = $AmNode['elementId'];
                        $node->navId = $nav->id;
                        $node->url = $AmNode['url'];
                        $node->classes = $AmNode['listClass'];
                        $node->newWindow = $AmNode['blank'];

                        $site = $sitesByLanguage[$AmNode['locale']] ?? null;

                        if ($site) {
                            $node->siteId = $site->id;
                        } else if (!$this->assignToDefaultSite) {
                            continue;
                        }

                        if ($AmNode['elementType'] === 'Entry') {
                           $node->type = \craft\elements\Entry::class;
                        } else if ($AmNode['elementType'] === 'Category') {
                           $node->type = \craft\elements\Category::class;
                        } else if ($AmNode['elementType'] === 'Asset') {
                           $node->type = \craft\elements\Asset::class;
                        }

                        if (Craft::$app->getElements()->saveElement($node, true, $this->propagate)) {
                            $this->processedNodes[$AmNode['id']] = [
                                'oldParent' => $AmNode['parentId'],
                                'newNode' => $node->id,
                                'siteId' => $node->siteId,
                            ];
                        } else {
                            echo "    > ERROR: Unable to save node `{$AmNode['name']}` ...\n";

                            Craft::dump($node->getErrors());
                        }
                    } catch (\Throwable $e) {
                        echo "    > ERROR: Unable to save node `{$AmNode['name']}` ...\n";

                        Craft::dump($e->getMessage());

                        continue;
                    } 
                }
            }

            // We have to update all parent's after all nodes are in, otherwise we can get issues (trying to set parent
            // of a node that might not exist yet)
            foreach ($this->processedNodes as $nodeInfo) {
                $newParent = $this->processedNodes[$nodeInfo['oldParent']] ?? null;

                if ($newParent) {
                    $node = Navigation::$plugin->nodes->getNodeById($nodeInfo['newNode'], $nodeInfo['siteId']);

                    if ($node) {
                        $node->newParentId = $newParent['newNode'];

                        if (Craft::$app->getElements()->saveElement($node, true, $this->propagate)) {
                            echo "    > Migrated node `{$node['title']}` ...\n";
                        } else {
                            echo "    > ERROR: Unable to re-save node `{$node['title']}` ...\n";

                            Craft::dump($node->getErrors());
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
