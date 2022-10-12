<?php
namespace verbb\navigation\elements\conditions;

use craft\elements\conditions\ElementCondition;
use craft\elements\conditions\LevelConditionRule;

class NodeCondition extends ElementCondition
{
    // Protected Methods
    // =========================================================================
    
    protected function conditionRuleTypes(): array
    {
        return array_merge(parent::conditionRuleTypes(), [
            LevelConditionRule::class,
            TypeConditionRule::class,
        ]);
    }
}
