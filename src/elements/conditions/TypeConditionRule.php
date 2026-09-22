<?php
namespace verbb\navigation\elements\conditions;

use verbb\navigation\Navigation;
use verbb\navigation\helpers\NodeTypeHelper;

use Craft;
use craft\base\conditions\BaseMultiSelectConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;

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

    public function setValues(array|string $values): void
    {
        if ($values === '') {
            parent::setValues($values);

            return;
        }

        // Field layout conditions persist their option values independently of node records,
        // so normalize legacy v3 classes when Craft hydrates the condition rule.
        $values = array_map(
            fn(string $value): string => NodeTypeHelper::resolveTypeClass($value) ?? $value,
            (array)$values,
        );

        parent::setValues($values);
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

        foreach (Navigation::$plugin->getNodeTypes()->getRegisteredNodeTypes() as $nodeType) {
            $options[get_class($nodeType)] = $nodeType->displayName();
        }

        return $options;
    }
}
