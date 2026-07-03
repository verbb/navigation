<?php
namespace verbb\navigation\helpers;

use verbb\navigation\elements\Node;

use Craft;
use craft\helpers\Cp;

class SiteSettings
{
    // Static Methods
    // =========================================================================

    public static function renderEditorHtml(Node $node): string
    {
        $options = [[
            'label' => Craft::t('navigation', 'Select a site'),
            'value' => '',
        ]];

        foreach (Craft::$app->getSites()->getEditableSites() as $site) {
            if ($site->hasUrls) {
                $options[] = [
                    'label' => $site->name,
                    'value' => (string)$site->id,
                ];
            }
        }

        return Craft::$app->getView()->namespaceInputs(
            fn() => Cp::selectFieldHtml([
                'label' => Craft::t('app', 'Site'),
                'instructions' => Craft::t('navigation', 'Select a site to use its Base URL.'),
                'id' => 'siteId',
                'name' => 'siteId',
                'value' => $node->data['siteId'] ?? '',
                'options' => $options,
            ]),
            'data',
        );
    }
}
