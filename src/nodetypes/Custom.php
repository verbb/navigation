<?php
namespace verbb\navigation\nodetypes;

use verbb\navigation\base\NodeType;
use verbb\navigation\helpers\NodeOutputSafety;

use Craft;
use craft\helpers\App;
use craft\helpers\Cp;

class Custom extends NodeType
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

    public function getEditorHtml(): ?string
    {
        return Cp::textFieldHtml([
            'label' => Craft::t('navigation', 'URL'),
            'instructions' => Craft::t('navigation', 'The URL for this node. Relative paths and http(s)/mailto/tel links are supported. Optional `{…}` tokens use a sandboxed Twig context.'),
            'id' => 'url',
            'name' => 'url',
            'value' => $this->node->getRawUrl(),
        ]);
    }

    public function getUrl(): ?string
    {
        $url = $this->node->getRawUrl();

        // Parse aliases and env variables
        $url = App::parseEnv($url);

        // Optional sandboxed Twig — authors are not full template authors.
        if ($url && str_contains($url, '{')) {
            $url = NodeOutputSafety::renderAuthorTemplate($url, NodeOutputSafety::authorTemplateObject());
        }

        return NodeOutputSafety::sanitizeUrl($url);
    }
}
