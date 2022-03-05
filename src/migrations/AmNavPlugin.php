<?php
namespace verbb\navigation\migrations;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\models\Nav as NavModel;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\helpers\Json;

use Throwable;

class AmNavPlugin extends Migration
{
    public bool $propagate = true;
    public bool $assignToDefaultSite = false;

    private array $processedNodes = [];

    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        try {
            if (!$this->db->tableExists('{{%amnav_navs}}')) {
                return true;
            }

            $sitesByLanguage = [];
            $siteSettings = [];

            foreach (Craft::$app->getSites()->getAllSites() as $site) {
                $languageHandle = strtolower(str_replace('-', '_', $site->language));

                $sitesByLanguage[$languageHandle] = $site;

                $siteSettings[$site->uid]['enabled'] = true;
            }

            $AmNavs = (new Query())
                ->select(['*'])
                ->from(['{{%amnav_navs}}'])
                ->all();

            foreach ($AmNavs as $key => $AmNav) {
                $nav = Navigation::$plugin->getNavs()->getNavByHandle($AmNav['handle']);

                echo "\n    > Migrating nav `{$AmNav['handle']}` ...\n";

                if (!$nav) {
                    $nav = new NavModel();
                }

                $nav->name = $AmNav['name'];
                $nav->handle = $AmNav['handle'];

                $settings = Json::decode($AmNav['settings']);
                $nav->maxLevels = $settings['maxLevels'] ?? '';
                $nav->sortOrder = $key;
                $nav->siteSettings = $siteSettings;

                if (!Navigation::$plugin->getNavs()->saveNav($nav)) {
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

                foreach ($AmNodes as $AmNode) {
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
                           $node->type = Entry::class;
                        } else if ($AmNode['elementType'] === 'Category') {
                           $node->type = Category::class;
                        } else if ($AmNode['elementType'] === 'Asset') {
                           $node->type = Asset::class;
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
                    } catch (Throwable $e) {
                        echo "    > ERROR: Unable to save node `{$AmNode['name']}` ...\n";

                        Craft::dump($e->getMessage());

                        echo "    > `{$this->getExceptionTraceAsString($e)}`";

                        continue;
                    } 
                }
            }

            // We have to update all parent's after all nodes are in, otherwise we can get issues (trying to set parent
            // of a node that might not exist yet)
            foreach ($this->processedNodes as $nodeInfo) {
                $newParent = $this->processedNodes[$nodeInfo['oldParent']] ?? null;

                if ($newParent) {
                    $node = Navigation::$plugin->getNodes()->getNodeById($nodeInfo['newNode'], $nodeInfo['siteId']);

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
        } catch (Throwable $e) {
            Craft::dump($e->getMessage());

            echo "    > `{$this->getExceptionTraceAsString($e)}`";
        }

        return true;
    }

    public function safeDown(): bool
    {
        return false;
    }

    private function getExceptionTraceAsString($exception): string
    {
        $rtn = "";
        $count = 0;

        foreach ($exception->getTrace() as $frame) {
            $args = "";

            if (isset($frame['args'])) {
                $args = array();

                foreach ($frame['args'] as $arg) {
                    if (is_string($arg)) {
                        $args[] = "'" . $arg . "'";
                    } elseif (is_array($arg)) {
                        $args[] = "Array";
                    } elseif (is_null($arg)) {
                        $args[] = 'NULL';
                    } elseif (is_bool($arg)) {
                        $args[] = ($arg) ? "true" : "false";
                    } elseif (is_object($arg)) {
                        $args[] = get_class($arg);
                    } elseif (is_resource($arg)) {
                        $args[] = get_resource_type($arg);
                    } else {
                        $args[] = $arg;
                    }
                }

                $args = implode(", ", $args);
            }

            $rtn .= sprintf( "#%s %s(%s): %s(%s)\n",
                                 $count,
                $frame['file'] ?? '[internal function]',
                $frame['line'] ?? '',
                                 (isset($frame['class']))  ? $frame['class'].$frame['type'].$frame['function'] : $frame['function'],
                                 $args );

            $count++;
        }

        return $rtn;
    }
}
