<?php
namespace verbb\navigation\nodetypes;

use verbb\navigation\base\NodeType;

use Craft;

class SiteType extends NodeType
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


    // Public Methods
    // =========================================================================

    public function getModalHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('navigation/_types/site/modal', [
            'node' => $this->node,
        ]);
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('navigation/_types/site/settings');
    }

    public function getUrl(): ?string
    {
        $data = $this->node->data ?? [];

        if ($data) {
            $siteId = $data['siteId'] ?? null;

            if ($siteId && $site = Craft::$app->getSites()->getSiteById($siteId)) {
                if ($site->hasUrls) {
                    return rtrim($site->getBaseUrl(), '/');
                }
            }
        }

        return null;
    }
}
