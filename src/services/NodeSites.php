<?php
namespace verbb\navigation\services;

use verbb\navigation\elements\Node;
use verbb\navigation\models\NodeSiteSettings as NodeSiteSettingsModel;
use verbb\navigation\records\NodeSiteSettings as NodeSiteSettingsRecord;

use craft\base\Component;
use craft\helpers\Db;

class NodeSites extends Component
{
    // Public Methods
    // =========================================================================

    public function getSettings(int $nodeId, int $siteId): ?NodeSiteSettingsModel
    {
        $row = NodeSiteSettingsRecord::find()
            ->where(['nodeId' => $nodeId, 'siteId' => $siteId])
            ->one();

        if (!$row) {
            return null;
        }

        return new NodeSiteSettingsModel([
            'id' => $row->id,
            'nodeId' => $row->nodeId,
            'siteId' => $row->siteId,
            'linkedElementSiteId' => $row->linkedElementSiteId,
            'url' => $row->url,
            'urlSuffix' => $row->urlSuffix,
            'uid' => $row->uid,
        ]);
    }

    public function getAllSettingsForNode(int $nodeId): array
    {
        $rows = NodeSiteSettingsRecord::find()
            ->where(['nodeId' => $nodeId])
            ->all();

        $settings = [];

        foreach ($rows as $row) {
            $model = $this->_modelFromRecord($row);
            $settings[$model->siteId] = $model;
        }

        return $settings;
    }

    public function saveSettings(NodeSiteSettingsModel $settings): bool
    {
        if (!$settings->nodeId || !$settings->siteId) {
            return false;
        }

        $record = NodeSiteSettingsRecord::find()
            ->where(['nodeId' => $settings->nodeId, 'siteId' => $settings->siteId])
            ->one() ?? new NodeSiteSettingsRecord();

        $record->nodeId = $settings->nodeId;
        $record->siteId = $settings->siteId;
        $record->linkedElementSiteId = $settings->linkedElementSiteId;
        $record->url = $settings->url;
        $record->urlSuffix = $settings->urlSuffix;

        return $record->save(false);
    }

    public function deleteSettings(int $nodeId, ?int $siteId = null): void
    {
        $condition = ['nodeId' => $nodeId];

        if ($siteId !== null) {
            $condition['siteId'] = $siteId;
        }

        Db::delete('{{%navigation_nodes_sites}}', $condition);
    }

    public function applyToNode(Node $node): void
    {
        if (!$node->id || !$node->siteId) {
            return;
        }

        $settings = $this->getSettings($node->id, $node->siteId);

        if (!$settings) {
            return;
        }

        $this->_applySettingsToNode($node, $settings);
    }

    public function applyToNodes(array $nodes, int $siteId): void
    {
        $nodeIds = [];

        foreach ($nodes as $node) {
            if ($node->id) {
                $nodeIds[] = $node->id;
            }
        }

        if (!$nodeIds) {
            return;
        }

        $rows = NodeSiteSettingsRecord::find()
            ->where(['nodeId' => $nodeIds, 'siteId' => $siteId])
            ->all();

        $indexed = [];

        foreach ($rows as $row) {
            $indexed[$row->nodeId] = $this->_modelFromRecord($row);
        }

        foreach ($nodes as $node) {
            if (!$node->id || !isset($indexed[$node->id])) {
                continue;
            }

            $this->_applySettingsToNode($node, $indexed[$node->id]);
        }
    }

    public function saveFromNode(Node $node): void
    {
        if (!$node->id || !$node->siteId) {
            return;
        }

        $settings = $this->getSettings($node->id, $node->siteId) ?? new NodeSiteSettingsModel([
            'nodeId' => $node->id,
            'siteId' => $node->siteId,
        ]);

        $settings->url = $node->getRawUrl();
        $settings->urlSuffix = $node->urlSuffix;
        $settings->linkedElementSiteId = $node->getElementSiteId();

        $this->saveSettings($settings);
    }


    // Private Methods
    // =========================================================================

    private function _applySettingsToNode(Node $node, NodeSiteSettingsModel $settings): void
    {
        if ($settings->url !== null) {
            $node->setUrl($settings->url);
        }

        if ($settings->urlSuffix !== null) {
            $node->urlSuffix = $settings->urlSuffix;
        }

        if ($settings->linkedElementSiteId) {
            $node->setElementSiteId($settings->linkedElementSiteId);
        }
    }

    private function _modelFromRecord(NodeSiteSettingsRecord $row): NodeSiteSettingsModel
    {
        return new NodeSiteSettingsModel([
            'id' => $row->id,
            'nodeId' => $row->nodeId,
            'siteId' => $row->siteId,
            'linkedElementSiteId' => $row->linkedElementSiteId,
            'url' => $row->url,
            'urlSuffix' => $row->urlSuffix,
            'uid' => $row->uid,
        ]);
    }
}
