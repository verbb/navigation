<?php
namespace verbb\navigation\fields;

use verbb\navigation\Navigation;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;

use yii\db\Schema;

class NavigationField extends Field
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('navigation', 'Navigation');
    }

    public static function defaultSelectionLabel(): string
    {
        return Craft::t('navigation', 'Select a navigation');
    }


    // Public Methods
    // =========================================================================

    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_TEXT;
    }

    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        $navs = Navigation::$plugin->getNavs()->getAllNavs();

        $options = [
            '' => Craft::t('navigation', 'Select a navigation'),
        ];

        foreach ($navs as $nav) {
            $options[$nav->handle] = $nav->name;
        }

        $id = Craft::$app->getView()->formatInputId($this->handle);
        $nameSpacedId = Craft::$app->getView()->namespaceInputId($id);

        return Craft::$app->getView()->renderTemplate('navigation/_field/input', [
            'id' => $id,
            'name' => $this->handle,
            'value' => $value,
            'options' => $options,
        ]);
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('navigation/_field/settings', [

        ]);
    }
    

    // Protected Methods
    // =========================================================================

    protected function optionsSettingLabel(): string
    {
        return Craft::t('navigation', 'Navigation Options');
    }
}
