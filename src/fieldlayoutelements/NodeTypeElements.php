<?php
namespace verbb\navigation\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\base\FieldLayoutElement;
use craft\fieldlayoutelements\BaseField;
use craft\helpers\ArrayHelper;
use craft\helpers\Component;
use craft\helpers\Cp;
use craft\helpers\Html;

class NodeTypeElements extends BaseField
{
    // Properties
    // =========================================================================

    public function attribute(): string
    {
        return '';
    }

    public function mandatory(): bool
    {
        return true;
    }

    public function hasCustomWidth(): bool
    {
        return false;
    }

    protected function showLabel(): bool
    {
        return false;
    }

    protected function selectorLabel(): ?string
    {
        return Craft::t('navigation', 'Node Type Fields');
    }

    protected function inputHtml(ElementInterface $element = null, bool $static = false): ?string
    {
        return null;
    }

    public function formHtml(ElementInterface $element = null, bool $static = false): ?string
    {
        if ($element->isElement()) {
            $classNameParts = explode('\\', $element->type);
            $typeClass = array_pop($classNameParts);

            return Cp::elementSelectFieldHtml([
                'label' => Craft::t('navigation', 'Linked to {element}', ['element' => $typeClass]),
                'instructions' => Craft::t('navigation', 'The element this node is linked to.'),
                'id' => 'linkedElementId',
                'name' => 'linkedElementId',
                'elements' => [$element->getElement()],
                'elementType' => $element->type,
                'sources' => '*',
                'showSiteMenu' => true,
                'limit' => 1,
                'modalStorageKey' => 'navigation.linkedElementId',
            ]);
        }

        if ($element->isNodeType()) {
            return $element->nodeType()->getModalHtml();
        }

        if ($element->isManual()) {
            return Cp::textFieldHtml([
                'label' => Craft::t('navigation', 'URL'),
                'instructions' => Craft::t('navigation', 'The URL for this navigation item.'),
                'id' => 'url',
                'name' => 'url',
                'value' => $element->getUrl(false),
            ]);
        }

        return null;
    }
}
