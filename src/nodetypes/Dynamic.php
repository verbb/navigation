<?php
namespace verbb\navigation\nodetypes;

use verbb\navigation\Navigation;
use verbb\navigation\base\NodeType;
use verbb\navigation\base\ProjectingNodeType;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\NodeTypeSchemaFields;
use verbb\navigation\models\ProjectedNode;

use Craft;
use craft\helpers\Cp;

class Dynamic extends NodeType implements ProjectingNodeType
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('navigation', 'Dynamic');
    }

    public static function hasUrl(): bool
    {
        return false;
    }

    public static function hasNewWindow(): bool
    {
        return false;
    }

    public static function getColor(): string
    {
        return '#0ea5e9';
    }

    public static function getTag(): string
    {
        return 'span';
    }

    public static function getAddNodeSchema(array $context): array
    {
        return [
            NodeTypeSchemaFields::dynamicSourceField(),
            NodeTypeSchemaFields::titleField(),
        ];
    }

    public static function getAddNodeDefaultData(): array
    {
        return [
            'dynamicSource' => Navigation::$plugin->getDynamicSources()->getDefaultHandle(),
        ];
    }


    // Public Methods
    // =========================================================================

    public function getEditorHtml(): ?string
    {
        if (!$this->node) {
            return null;
        }

        $view = Craft::$app->getView();
        $sourceInputId = $view->namespaceInputId('dynamicSource');

        // Source swap must re-run alwaysRefresh so provider-specific fields replace cleanly.
        $view->registerJs(<<<JS
(() => {
const \$sourceInput = $('#$sourceInputId');
const getEditor = () => {
    const \$editorContainer = \$sourceInput.closest('[data-element-editor]');
    if (\$editorContainer.length) {
        return \$editorContainer.data('elementEditor');
    }
    return \$sourceInput.closest('form').data('elementEditor');
};

\$sourceInput.off('change.navigationDynamicSource').on('change.navigationDynamicSource', () => {
    const editor = getEditor();
    if (editor) {
        editor.checkForm(true);
    }
});
})();
JS);

        $html = Cp::selectFieldHtml([
            'label' => Craft::t('navigation', 'Source'),
            'instructions' => Craft::t('navigation', 'Choose which elements to project as child menu items.'),
            'id' => 'dynamicSource',
            'name' => 'data[dynamicSource]',
            'value' => $this->node->data['dynamicSource'] ?? '',
            'options' => Navigation::$plugin->getDynamicSources()->getProviderOptions(),
            'required' => true,
        ]);

        $class = Navigation::$plugin->getDynamicSources()->getProviderClassForNode($this->node);

        if ($class) {
            $html .= $class::renderSlideoutHtml($this->node);
        }

        return $html;
    }

    public function getDefaultTitle(): string
    {
        $class = $this->_providerClass();

        if ($class && ($title = $class::getDefaultTitle($this->node))) {
            return $title;
        }

        return parent::getDefaultTitle();
    }

    public function getTypeLabel(): string
    {
        $class = $this->_providerClass();

        if ($class && ($label = $class::getTypeLabel($this->node))) {
            return $label;
        }

        return static::displayName();
    }

    public function beforeSaveNode(bool $isNew): bool
    {
        $class = $this->_providerClass();

        if (!$class) {
            $this->node->addError('data', Craft::t('navigation', 'Please select a dynamic source.'));

            return false;
        }

        $class::applyPostData($this->node);

        return $class::validateNode($this->node);
    }

    public function getProjectedChildren(Node $parent, int $siteId): array
    {
        return Navigation::$plugin->getDynamicSources()->getProjectedChildren($parent, $siteId);
    }


    // Private Methods
    // =========================================================================

    private function _providerClass(): ?string
    {
        if (!$this->node) {
            return null;
        }

        return Navigation::$plugin->getDynamicSources()->getProviderClassForNode($this->node);
    }
}
