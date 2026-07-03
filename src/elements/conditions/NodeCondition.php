<?php
namespace verbb\navigation\elements\conditions;

use craft\elements\conditions\ElementCondition;
use craft\elements\conditions\LevelConditionRule;

class NodeCondition extends ElementCondition
{
    // Protected Methods
    // =========================================================================

    protected function selectableConditionRules(): array
    {
        return array_merge(parent::selectableConditionRules(), [
            LevelConditionRule::class,
            TypeConditionRule::class,
        ]);
    }
}
