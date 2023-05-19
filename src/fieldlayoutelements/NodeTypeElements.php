<?php
namespace verbb\navigation\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseField;
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

            $siteId = $element->getElement()->siteId ?? null;
            $html = Html::hiddenInput('linkedElementSiteId', $siteId, [
                'id' => 'linkedElementSiteId',
            ]);

            $nav = $element->getNav();
            $sources = $nav->permissions[$element->type]['permissions'] ?? '*';

            $html .= Cp::elementSelectFieldHtml([
                'label' => Craft::t('navigation', 'Linked to {element}', ['element' => $typeClass]),
                'instructions' => Craft::t('navigation', 'The element this node is linked to.'),
                'id' => 'linkedElementId',
                'name' => 'linkedElementId',
                'elements' => [$element->getElement()],
                'elementType' => $element->type,
                'sources' => $sources,
                'showSiteMenu' => true,
                'required' => true,
                'limit' => 1,
                'modalStorageKey' => 'navigation.linkedElementId',
            ]);

            $namespace = Craft::$app->getView()->getNamespace();

            $html .= "<script>new Craft.Navigation.ElementSelect('#" . $namespace ."-linkedElementId', '#" . $namespace ."-linkedElementSiteId')</script>";

            return $html;
        }

        if ($nodeType = $element->nodeType()) {
            return $nodeType->getModalHtml();
        }

        return null;
    }
}
