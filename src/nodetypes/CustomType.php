<?php
namespace verbb\navigation\nodetypes;

use verbb\navigation\base\NodeType;

use Craft;
use craft\helpers\App;

class CustomType extends NodeType
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('navigation', 'Custom URL');
    }

    public static function hasTitle(): bool
    {
        return true;
    }

    public static function hasUrl(): bool
    {
        return true;
    }

    public static function hasNewWindow(): bool
    {
        return true;
    }

    public static function getColor(): string
    {
        return '#0d78f2';
    }


    // Public Methods
    // =========================================================================

    public function getModalHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('navigation/_types/custom/modal', [
            'node' => $this->node,
        ]);
    }

    public function getUrl(): ?string
    {
        $url = $this->node->getRawUrl();

        // Parse aliases and env variables
        $url = App::parseEnv($url);

        // Allow twig support
        if ($url && strstr($url, '{')) {
            $object = $this->_getObject();
            $url = Craft::$app->getView()->renderObjectTemplate($url, $object);
        }

        return $url;
    }


    // Private Methods
    // =========================================================================

    private function _getObject(): array
    {
        return [
            'currentUser' => Craft::$app->getUser()->getIdentity(),
        ];
    }
}
