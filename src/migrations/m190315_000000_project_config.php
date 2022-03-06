<?php
namespace verbb\navigation\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;

class m190315_000000_project_config extends Migration
{
    public function safeUp(): bool
    {
        // Don't make the same config changes twice
        $projectConfig = Craft::$app->getProjectConfig();
        $schemaVersion = $projectConfig->get('plugins.navigation.schemaVersion', true);

        if (version_compare($schemaVersion, '1.0.11', '>=')) {
            return true;
        }

        $projectConfig->set('navigation.navs', $this->_getNavData());

        return true;
    }

    public function safeDown(): bool
    {
        echo "m190315_000000_project_config cannot be reverted.\n";

        return false;
    }

    private function _getNavData(): array
    {
        $navRows = (new Query())
            ->select([
                'navs.name',
                'navs.handle',
                'navs.instructions',
                'navs.sortOrder',
                'navs.propagateNodes',
                'navs.uid',
                'structures.uid AS structure',
                'structures.maxLevels AS structureMaxLevels',
            ])
            ->from(['{{%navigation_navs}} navs'])
            ->leftJoin('{{%structures}} structures', '[[structures.id]] = [[navs.structureId]]')
            ->all();

        $navData = [];

        foreach ($navRows as $nav) {
            if (!empty($nav['structure'])) {
                $nav['structure'] = [
                    'uid' => $nav['structure'],
                    'maxLevels' => $nav['structureMaxLevels'],
                ];
            } else {
                unset($nav['structure']);
            }

            $uid = $nav['uid'];
            unset($nav['structureMaxLevels'], $nav['uid']);

            $navData[$uid] = $nav;
        }

        return $navData;
    }
}
