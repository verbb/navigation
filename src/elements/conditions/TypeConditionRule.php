<?php
namespace verbb\navigation\elements\conditions;

use verbb\navigation\Navigation;

use Craft;
use craft\base\conditions\BaseMultiSelectConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\StringHelper;

class TypeConditionRule extends BaseMultiSelectConditionRule implements ElementConditionRuleInterface
{
    // Public Methods
    // =========================================================================

    public function getLabel(): string
    {
        return Craft::t('navigation', 'Node Type');
    }

    public function getExclusiveQueryParams(): array
    {
        return ['type'];
    }

    public function modifyQuery(ElementQueryInterface $query): void
    {

    }

    public function matchElement(ElementInterface $element): bool
    {
        return $this->matchValue((string)$element->type);
    }


    // Protected Methods
    // =========================================================================

    protected function options(): array
    {
        $options = [];

        foreach (Navigation::$plugin->getElements()->getRegisteredElements() as $registeredElement) {
            $options[$registeredElement['type']] = $registeredElement['label'];
        }

        foreach (Navigation::$plugin->getNodeTypes()->getAllNodeTypes() as $nodeType) {
            $options[get_class($nodeType)] = $nodeType->displayName();
        }

        return $options;
    }
}
