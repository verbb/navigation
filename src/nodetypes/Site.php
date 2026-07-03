<?php
namespace verbb\navigation\nodetypes;

use verbb\navigation\base\NodeType;
use verbb\navigation\helpers\NodeTypeSchemaFields;
use verbb\navigation\helpers\SiteSettings;

use Craft;
use craft\models\Site as CraftSite;

class Site extends NodeType
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('navigation', 'Site');
    }

    public static function hasTitle(): bool
    {
        return true;
    }

    public static function hasUrl(): bool
    {
        return false;
    }

    public static function hasNewWindow(): bool
    {
        return false;
    }

    public static function getColor(): string
    {
        return '#737df8';
    }

    public static function getAddNodeSchema(array $context): array
    {
        return [
            NodeTypeSchemaFields::siteIdField(),
            NodeTypeSchemaFields::titleField(),
        ];
    }

    public static function getAddNodeDefaultData(): array
    {
        return ['siteId' => ''];
    }


    // Public Methods
    // =========================================================================

    public function getEditorHtml(): ?string
    {
        return SiteSettings::renderEditorHtml($this->node);
    }

    public function getDefaultTitle(): string
    {
        if ($site = $this->_getSite()) {
            if ($site->hasUrls) {
                return $site->name;
            }
        }

        return parent::getDefaultTitle();
    }

    public function getUrl(): ?string
    {
        if ($site = $this->_getSite()) {
            if ($site->hasUrls) {
                return rtrim($site->getBaseUrl(), '/');
            }
        }

        return null;
    }


    // Private Methods
    // =========================================================================

    private function _getSite(): ?CraftSite
    {
        $data = $this->node->data ?? [];

        if ($data) {
            $siteId = $data['siteId'] ?? null;

            if ($siteId && $site = Craft::$app->getSites()->getSiteById($siteId)) {
                return $site;
            }
        }

        return null;
    }
}
