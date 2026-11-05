<?php

declare(strict_types=1);

use craft\elements\User;
use craft\mcp\elements\ElementAdapterRegistry;
use Mcp\Exception\ToolCallException;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\integrations\NodeMcpAdapter;
use verbb\navigation\integrations\NodeMcpTools;
use verbb\navigation\nodetypes\Custom;

uses()->group('mcp');

beforeEach(function() {
    $this->identity = Craft::$app->getUser()->getIdentity();
    if (!class_exists(ElementAdapterRegistry::class)) {
        $this->markTestSkipped('Craft’s MCP server needs Craft 5.12 or later.');
    }

    $this->admin = User::find()->admin()->one();
    Craft::$app->getUser()->setIdentity($this->admin);

    $this->adapter = fn(): NodeMcpAdapter => Craft::$app->get('mcp')->getElementAdapterRegistry()->find('navigation-nodes');
    $this->tools = fn(): NodeMcpTools => Craft::$app->get('mcp')->getCapabilityContainer()->get(NodeMcpTools::class);
    $this->titles = fn($menu, ?Node $parent = null): array => ($parent
        ? Node::find()->menuId($menu->id)->descendantOf($parent->id)->descendantDist(1)
        : Node::find()->menuId($menu->id)->level(1))->status(null)->collect()->map(fn(Node $node) => $node->title)->all();
});

afterEach(function() {
    Craft::$app->getUser()->setIdentity($this->identity ?? null);
});

it('registers the node adapter under its own handle and the node reference handle', function() {
    $registry = Craft::$app->get('mcp')->getElementAdapterRegistry();

    expect($registry->handles())->toContain('navigation-nodes')
        ->and($registry->find('node'))->toBeInstanceOf(NodeMcpAdapter::class);
});

it('creates and updates nodes by menu handle', function() {
    $menu = F::menu();

    $created = ($this->adapter)()->create(['menuHandle' => $menu->handle, 'type' => Custom::class, 'title' => 'Contact', 'url' => '/contact'], [], $this->admin);
    $node = Node::find()->id($created['element']['id'])->one();

    expect($node->menuId)->toBe($menu->id)
        ->and($node->getUrl())->toBe('/contact');

    ($this->adapter)()->update($node, ['title' => 'Get in touch'], [], $this->admin);

    expect(Node::find()->id($node->id)->one()->title)->toBe('Get in touch');
});

it('requires a menu for new nodes', function() {
    ($this->adapter)()->create(['type' => Custom::class, 'title' => 'Orphan', 'url' => '/orphan'], [], $this->admin);
})->throws(ToolCallException::class, 'Provide menuId or menuHandle');

it('finds disabled nodes', function() {
    $node = F::disabledCustomNode(F::menu(), 'Hidden', '/hidden');

    expect(($this->adapter)()->find($node->id, null, null)?->id)->toBe($node->id);
});

it('lists the menus the user can manage', function() {
    $menu = F::menu();

    expect(array_column(($this->tools)()->listMenus()['menus'], 'handle'))->toContain($menu->handle);
});

it('moves nodes before and after siblings', function() {
    $menu = F::menu();
    $one = F::customNode($menu, 'One', '/one');
    $two = F::customNode($menu, 'Two', '/two');
    $three = F::customNode($menu, 'Three', '/three');

    ($this->tools)()->moveNode($three->id, 'before', $one->id);
    expect(($this->titles)($menu))->toBe(['Three', 'One', 'Two']);

    ($this->tools)()->moveNode($three->id, 'after', $two->id);
    expect(($this->titles)($menu))->toBe(['One', 'Two', 'Three']);
});

it('moves nodes to the start or end of a parent and the top level', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    $first = F::customNode($menu, 'First', '/first', $parent);
    $loose = F::customNode($menu, 'Loose', '/loose');

    ($this->tools)()->moveNode($loose->id, 'prepend', $parent->id);
    expect(($this->titles)($menu, $parent))->toBe(['Loose', 'First']);

    ($this->tools)()->moveNode($loose->id, 'append', $parent->id);
    expect(($this->titles)($menu, $parent))->toBe(['First', 'Loose']);

    ($this->tools)()->moveNode($loose->id, 'prepend');
    expect(($this->titles)($menu))->toBe(['Loose', 'Parent']);
});

it('refuses moves across menus', function() {
    $node = F::customNode(F::menu(), 'Here', '/here');
    $other = F::customNode(F::menu(), 'There', '/there');

    ($this->tools)()->moveNode($node->id, 'after', $other->id);
})->throws(ToolCallException::class, 'different menu');

it('needs a target for before and after', function() {
    $node = F::customNode(F::menu(), 'Alone', '/alone');

    ($this->tools)()->moveNode($node->id, 'before');
})->throws(ToolCallException::class, 'needs targetNodeId');

it('refuses to move a node beneath its own descendant', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    $child = F::customNode($menu, 'Child', '/child', $parent);

    ($this->tools)()->moveNode($parent->id, 'append', $child->id);
})->throws(ToolCallException::class, 'descendant');

it('enforces the menu’s maximum levels', function() {
    $menu = F::menu();
    $menu->maxLevels = 1;
    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    $one = F::customNode($menu, 'One', '/one');
    $two = F::customNode($menu, 'Two', '/two');

    ($this->tools)()->moveNode($two->id, 'append', $one->id);
})->throws(ToolCallException::class);

it('hides menus and refuses moves for users without menu permissions', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Guarded', '/guarded');
    $user = new User(['username' => uniqid('mcp-'), 'email' => uniqid('mcp-') . '@example.test']);
    expect(Craft::$app->getElements()->saveElement($user))->toBeTrue();
    Craft::$app->getUser()->setIdentity($user);

    expect(array_column(($this->tools)()->listMenus()['menus'], 'handle'))->not->toContain($menu->handle);

    ($this->tools)()->moveNode($node->id, 'prepend');
})->throws(ToolCallException::class, 'not authorized');

it('returns a menu as a nested tree with disabled nodes', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    F::customNode($menu, 'Child', '/child', $parent);
    F::disabledCustomNode($menu, 'Hidden', '/hidden');

    $tree = ($this->tools)()->menuTree($menu->handle);

    expect($tree['menuId'])->toBe($menu->id)
        ->and(array_column($tree['nodes'], 'title'))->toBe(['Parent', 'Hidden'])
        ->and(array_column($tree['nodes'][0]['children'], 'title'))->toBe(['Child'])
        ->and($tree['nodes'][1]['enabled'])->toBeFalse();
});

it('refuses menu trees to users without menu permissions', function() {
    $menu = F::menu();
    $user = new User(['username' => uniqid('mcp-'), 'email' => uniqid('mcp-') . '@example.test']);
    expect(Craft::$app->getElements()->saveElement($user))->toBeTrue();
    Craft::$app->getUser()->setIdentity($user);

    ($this->tools)()->menuTree($menu->handle);
})->throws(ToolCallException::class, 'you can manage');
