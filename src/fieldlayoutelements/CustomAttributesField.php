<?php
namespace verbb\navigation\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseNativeField;
use craft\helpers\Cp;

class CustomAttributesField extends BaseNativeField
{
    // Properties
    // =========================================================================

    public string $attribute = 'customAttributes';
    public bool $requirable = true;


    // Public Methods
    // =========================================================================

    public function defaultLabel(?ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('navigation', 'Custom Attributes');
    }

    public function instructions(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('navigation', 'Additional attributes for this node.');
    }

    public function inputHtml(?ElementInterface $element = null, bool $static = false): ?string
    {
        return Cp::editableTableFieldHtml([
            'id' => $this->attribute,
            'name' => $this->attribute,
            'cols' => [
                'attribute' => [
                    'type' => 'singleline',
                    'heading' => Craft::t('navigation', 'Attribute'),
                ],
                'value' => [
                    'type' => 'singleline',
                    'heading' => Craft::t('navigation', 'Value'),
                    'code' => true,
                ],
            ],
            'rows' => $element->customAttributes ?? [],
            'allowAdd' => true,
            'allowDelete' => true,
            'allowReorder' => true,
        ]);
    }
}
