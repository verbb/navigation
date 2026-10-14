<?php

declare(strict_types=1);

use verbb\navigation\helpers\StructureMovePlanner;

function flatMoves(array $ids): array
{
    $moves = [];
    $previousId = null;

    foreach ($ids as $id) {
        $moves[] = ['elementId' => $id, 'parentId' => null, 'prevId' => $previousId];
        $previousId = $id;
    }

    return $moves;
}

it('plans one structure operation for a single-node reorder', function(array $desiredIds, int $elementId, ?int $prevId) {
    $current = flatMoves(range(1, 100));
    $plan = StructureMovePlanner::plan($current, flatMoves($desiredIds));

    expect($plan)->toBe([[
        'elementId' => $elementId,
        'parentId' => null,
        'prevId' => $prevId,
    ]]);
})->with([
    'last to first' => [array_merge([100], range(1, 99)), 100, null],
    'first to last' => [array_merge(range(2, 100), [1]), 1, 100],
    'middle before sibling' => [array_merge(range(1, 49), [75], range(50, 74), range(76, 100)), 75, 49],
]);

it('plans independent parent changes without replaying unaffected siblings', function() {
    $current = [
        ['elementId' => 1, 'parentId' => null, 'prevId' => null],
        ['elementId' => 2, 'parentId' => 1, 'prevId' => null],
        ['elementId' => 3, 'parentId' => 1, 'prevId' => 2],
        ['elementId' => 4, 'parentId' => null, 'prevId' => 1],
        ['elementId' => 5, 'parentId' => 4, 'prevId' => null],
    ];
    $desired = [
        ['elementId' => 1, 'parentId' => null, 'prevId' => null],
        ['elementId' => 3, 'parentId' => 1, 'prevId' => null],
        ['elementId' => 4, 'parentId' => null, 'prevId' => 1],
        ['elementId' => 5, 'parentId' => 4, 'prevId' => null],
        ['elementId' => 2, 'parentId' => 4, 'prevId' => 5],
    ];

    expect(StructureMovePlanner::plan($current, $desired))->toBe([[
        'elementId' => 2,
        'parentId' => 4,
        'prevId' => 5,
    ]]);
});

