<?php
namespace verbb\navigation\fieldlayoutelements;

use verbb\navigation\Navigation;
use verbb\navigation\base\ElementNodeType;
use verbb\navigation\helpers\ElementPickerHelper;
use verbb\navigation\helpers\MenuPermissions;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseField;
use craft\helpers\Cp;
use craft\helpers\Html;

class NodeTypeElements extends BaseField
{
    // Public Methods
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

    /**
     * Always re-render when the element editor checks the form (e.g. node type changes).
     *
     * @see https://github.com/verbb/navigation/issues/326
     */
    public function alwaysRefresh(): bool
    {
        return true;
    }

    public function formHtml(ElementInterface $element = null, bool $static = false): ?string
    {
        if ($element->isElement()) {
            $nodeType = $element->nodeType();

            if (!$nodeType instanceof ElementNodeType) {
                return null;
            }

            $elementType = $nodeType::getElementType();
            $elementDisplayName = Craft::t('site', $elementType::displayName());

            $siteId = $element->getElement()->siteId ?? null;
            $html = Html::hiddenInput('linkedElementSiteId', $siteId, [
                'id' => 'linkedElementSiteId',
            ]);

            $nav = Navigation::$plugin->getMenus()->getMenuById($element->menuId);
            $permissions = $nav->permissions ?? [];
            $sources = ElementPickerHelper::filterSourcesForUser(
                $elementType,
                MenuPermissions::getTypeSources($permissions, $element->type),
            );
            $pickerConfig = ElementPickerHelper::getPickerConfig($permissions, $element->type, $elementType);
            $pickerCondition = null;

            if (isset($pickerConfig['condition'])) {
                $pickerCondition = Craft::$app->getConditions()->createCondition(array_merge(
                    ['class' => $elementType::createCondition()::class, 'elementType' => $elementType],
                    $pickerConfig['condition'],
                ));
            }

            $html .= Cp::elementSelectFieldHtml([
                'label' => Craft::t('navigation', 'Linked to {element}', ['element' => $elementDisplayName]),
                'instructions' => Craft::t('navigation', 'The element this node is linked to.'),
                'id' => 'linkedElementId',
                'name' => 'linkedElementId',
                'elements' => $element->getElement() ? [$element->getElement()] : [],
                'elementType' => $elementType,
                'sources' => $sources,
                'criteria' => $pickerConfig['criteria'] ?? null,
                'condition' => $pickerCondition,
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
            return $nodeType->getEditorHtml();
        }

        return null;
    }


    // Protected Methods
    // =========================================================================

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
}
