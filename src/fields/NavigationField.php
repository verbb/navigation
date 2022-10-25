<?php
namespace verbb\navigation\fields;

use verbb\navigation\Navigation;
use verbb\navigation\gql\arguments\NodeArguments;
use verbb\navigation\gql\interfaces\NodeInterface;
use verbb\navigation\gql\resolvers\NodeResolver;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Html;

use yii\db\Schema;

use GraphQL\Type\Definition\Type;

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

        $id = Html::id($this->handle);

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

    public function getContentGqlType(): Type|array
    {
        return [
            'name' => $this->handle,
            'type' => Type::listOf(NodeInterface::getType()),
            'args' => NodeArguments::getArguments(),
            'resolve' => NodeResolver::class . '::resolve',
        ];
    }
    

    // Protected Methods
    // =========================================================================

    protected function optionsSettingLabel(): string
    {
        return Craft::t('navigation', 'Navigation Options');
    }
}
