<?php

use craft\elements\Entry as EntryElement;
use verbb\navigation\elements\conditions\TypeConditionRule;
use verbb\navigation\elements\Node;
use verbb\navigation\nodetypes\Entry;
use verbb\navigation\nodetypes\Passive;

test('node type conditions normalize legacy v3 values', function() {
    $rule = new TypeConditionRule();
    $rule->setValues([
        'verbb\\navigation\\nodetypes\\PassiveType',
        EntryElement::class,
        'modules\\site\\nodetypes\\Special',
    ]);

    expect($rule->getValues())->toBe([
        Passive::class,
        Entry::class,
        'modules\\site\\nodetypes\\Special',
    ])->and($rule->getConfig()['values'])->toBe([
        Passive::class,
        Entry::class,
        'modules\\site\\nodetypes\\Special',
    ]);
});

test('legacy passive conditions match v4 passive nodes', function() {
    $rule = new TypeConditionRule();
    $rule->setValues('verbb\\navigation\\nodetypes\\PassiveType');

    $node = new Node([
        'type' => Passive::class,
    ]);

    expect($rule->matchElement($node))->toBeTrue();
});
