<?php
namespace verbb\navigation\helpers;

use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseField;
use craft\fieldlayoutelements\TitleField;
use craft\models\FieldLayout;

class MenuContentFieldLayout
{
    // Public Methods
    // =========================================================================

    /**
     * Menu name lives on menu settings (`MenuSettings::name`), not in menu content fields.
     */
    public static function withoutTitle(?FieldLayout $layout): ?FieldLayout
    {
        if (!$layout) {
            return null;
        }

        $layout = clone $layout;

        foreach ($layout->getTabs() as $tab) {
            $elements = array_values(array_filter(
                $tab->getElements(),
                static fn($element) => !self::_isTitleElement($element),
            ));

            $tab->setElements($elements);
        }

        return $layout;
    }

    public static function hasCustomFields(?FieldLayout $layout): bool
    {
        $layout = self::withoutTitle($layout);

        return $layout && !empty($layout->getCustomFields());
    }

    /**
     * Each field layout tab becomes a top-level builder tab (Nodes | Content | …).
     */
    public static function getBuilderFormTabs(FieldLayout $layout, ElementInterface $element): array
    {
        $form = $layout->createForm($element, false, [
            'registerDeltas' => true,
        ]);

        $tabs = [];

        foreach ($form->tabs as $formTab) {
            $tabs[] = [
                'id' => 'pane-menu-' . $formTab->getId(),
                'label' => $formTab->getName(),
                'html' => $formTab->getContent(),
                'hasErrors' => $formTab->hasErrors,
            ];
        }

        return $tabs;
    }

    public static function stripTitleFromConfig(array $config): array
    {
        foreach ($config['tabs'] ?? [] as $tabIndex => $tab) {
            $config['tabs'][$tabIndex]['elements'] = array_values(array_filter(
                $tab['elements'] ?? [],
                static fn(array $element) => !self::_isTitleConfig($element),
            ));
        }

        return $config;
    }


    // Private Methods
    // =========================================================================

    private static function _isTitleElement(mixed $element): bool
    {
        if ($element instanceof TitleField) {
            return true;
        }

        return $element instanceof BaseField && $element->attribute() === 'title';
    }

    private static function _isTitleConfig(array $element): bool
    {
        return ($element['type'] ?? '') === TitleField::class
            || ($element['attribute'] ?? '') === 'title';
    }
}
