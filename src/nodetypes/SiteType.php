<?php
namespace verbb\navigation\nodetypes;

use Craft;

use verbb\navigation\base\NodeType;

class SiteType extends NodeType
{
    // Static
    // =========================================================================

    public static function displayName(): string
    {
        return 'Site';
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

    public static function hasClasses(): bool
    {
        return false;
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
