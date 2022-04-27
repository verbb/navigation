<?php
namespace verbb\navigation\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseNativeField;
use craft\helpers\Cp;

class NewWindowField extends BaseNativeField
{
    // Properties
    // =========================================================================

    public string $attribute = 'newWindow';
    public bool $requirable = true;


    // Public Methods
    // =========================================================================

    public function defaultLabel(?ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('navigation', 'New Window');
    }

    public function instructions(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('navigation', 'Whether to open this navigation item in a new window.');
    }

    public function inputHtml(?ElementInterface $element = null, bool $static = false): ?string
    {
        return Cp::lightswitchHtml([
            'name' => $this->attribute,
            'on' => $element->newWindow,
        ]);
    }
}
