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
            $hidden = Html::hiddenInput('linkedElementSiteId', $siteId, [
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

            $fieldHtml = Cp::elementSelectFieldHtml([
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
            $script = "<script>new Craft.Navigation.ElementSelect('#" . $namespace ."-linkedElementId', '#" . $namespace ."-linkedElementSiteId')</script>";

            // Keep a single `.field` root for alwaysRefresh + flex-fields spacing
            // (nesting `.field` inside a wrapper re-applies Craft’s 24px field margins).
            return self::_injectIntoFieldRoot($fieldHtml, $hidden, $script);
        }

        if ($nodeType = $element->nodeType()) {
            $html = $nodeType->getEditorHtml();

            return $html ? self::_wrapEditorHtml($html) : null;
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


    // Private Methods
    // =========================================================================

    /**
     * Craft’s FieldLayout attaches `data-layout-element` via modifyTagAttributes on the first root
     * tag only. Multi-root editor HTML (Source + Section + …) left siblings behind on alwaysRefresh
     * replaceWith — wrapping once keeps the whole block swappable.
     *
     * Nested `.field` margins are reset in `node-type-fields.css` (flex-fields only zeroes
     * margins on direct children).
     */
    private static function _wrapEditorHtml(string $html): string
    {
        return Html::tag('div', $html, [
            'class' => 'navigation-node-type-fields',
        ]);
    }

    /**
     * Puts site-id + ElementSelect bootstrap inside the element-select `.field` so that field
     * remains the sole flex-fields child (correct spacing + alwaysRefresh target).
     */
    private static function _injectIntoFieldRoot(string $fieldHtml, string $prefixHtml, string $suffixHtml): string
    {
        if (!preg_match('/^(<div\b[^>]*>)/i', $fieldHtml, $match)) {
            return self::_wrapEditorHtml($prefixHtml . $fieldHtml . $suffixHtml);
        }

        $opening = $match[1];
        $rest = substr($fieldHtml, strlen($opening));
        $closingPos = strrpos($rest, '</div>');

        if ($closingPos === false) {
            return self::_wrapEditorHtml($prefixHtml . $fieldHtml . $suffixHtml);
        }

        return $opening
            . $prefixHtml
            . substr($rest, 0, $closingPos)
            . $suffixHtml
            . substr($rest, $closingPos);
    }
}
