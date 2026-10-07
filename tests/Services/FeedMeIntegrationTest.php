<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;

it('renders menu titles in the Feed Me feed list', function() {
    $menu = F::menu();
    $source = file_get_contents(dirname(__DIR__, 2) . '/src/templates/_integrations/feed-me/column.html');
    $template = Craft::$app->view->getTwig()->createTemplate($source);

    $html = $template->render([
        'elementType' => Node::class,
        'feed' => [
            'elementGroup' => [Node::class => $menu->id],
        ],
    ]);

    expect(trim($html))->toBe($menu->name);
});

it('renders an empty Feed Me column when its menu no longer exists', function() {
    $source = file_get_contents(dirname(__DIR__, 2) . '/src/templates/_integrations/feed-me/column.html');
    $template = Craft::$app->view->getTwig()->createTemplate($source);

    $html = $template->render([
        'elementType' => Node::class,
        'feed' => [
            'elementGroup' => [Node::class => 999999999],
        ],
    ]);

    expect(trim($html))->toBe('');
});
