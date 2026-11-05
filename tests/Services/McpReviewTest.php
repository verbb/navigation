<?php

declare(strict_types=1);

use craft\elements\User;
use craft\mcp\elements\ElementAdapterRegistry;
use craft\mcp\events\ElementSerializing;
use craft\mcp\serializers\ElementSerializer;
use Mcp\Exception\ToolCallException;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\integrations\NodeMcpAdapter;
use verbb\navigation\integrations\NodeMcpTools;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\nodetypes\Custom;
use yii\base\Event;

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
    $this->editor = function($menu, array $extra = []): User {
        $user = new User(['username' => uniqid('mcp-review-'), 'email' => uniqid('mcp-review-') . '@example.test']);
        expect(Craft::$app->getElements()->saveElement($user))->toBeTrue();
        Craft::$app->set('userPermissions', new \craft\services\UserPermissions());
        expect(Craft::$app->getUserPermissions()->saveUserPermissions($user->id, [
            'accessCp', 'navigation-manageMenu:' . $menu->uid,
            'editSite:' . Craft::$app->getSites()->getPrimarySite()->uid, ...$extra,
        ]))->toBeTrue();
        return User::find()->id($user->id)->one();
    };
});

afterEach(function() {
    Craft::$app->getUser()->setIdentity($this->identity ?? null);
});

it('resolves custom field schemas for a new node from its menu', function() {
    $menu = F::menu();
    $field = F::addPlainTextFieldToMenu($menu);
    $element = ($this->adapter)()->fieldSchemaElement(null, ['menuId' => $menu->id], $this->admin);
    expect($element->getFieldLayout()->getCustomFieldElements()[0]->getField()->handle)->toBe($field->handle);
});

it('does not expand linked content the menu editor cannot view', function(bool $dynamic) {
    $menu = F::menu();
    $section = F::entrySection();
    $entry = F::entries(1, $section)[0];
    $dynamic ? F::dynamicSectionNode($menu, $section) : F::entryNode($menu, $entry);
    $editor = ($this->editor)($menu);
    Craft::$app->getUser()->setIdentity($editor);
    expect(Craft::$app->getElements()->canView($entry, $editor))->toBeFalse();
    $tree = ($this->tools)()->menuTree($menu->handle, withLinkedElements: true);
    $row = $dynamic ? $tree['nodes'][0]['children'][0] : $tree['nodes'][0];
    expect($row['element'])->toBeNull();
})->with([false, true]);

it('uses Craft MCP serialization hooks for tree nodes and linked content', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    F::entryNode($menu, $entry);
    $handler = static function(ElementSerializing $event): void {
        $event->serializedData['mcpSerializationApplied'] = true;
        unset($event->serializedData['url']);
    };
    Event::on(ElementSerializer::class, ElementSerializer::EVENT_SERIALIZING, $handler);
    try {
        $tree = ($this->tools)()->menuTree($menu->handle, withLinkedElements: true);
        expect($tree['nodes'][0]['mcpSerializationApplied'] ?? false)->toBeTrue()
            ->and($tree['nodes'][0]['element']['mcpSerializationApplied'] ?? false)->toBeTrue()
            ->and($tree['nodes'][0])->not->toHaveKey('url')
            ->and($tree['nodes'][0]['element'])->not->toHaveKey('url');
    } finally {
        Event::off(ElementSerializer::class, ElementSerializer::EVENT_SERIALIZING, $handler);
    }
});

it('only advertises sites the menu editor can use', function() {
    F::secondarySite();
    $menu = F::menu();
    Craft::$app->getUser()->setIdentity(($this->editor)($menu));
    $menus = array_column(($this->tools)()->listMenus()['menus'], null, 'id');
    expect(array_map('intval', $menus[$menu->id]['siteIds']))->toBe([Craft::$app->getSites()->getPrimarySite()->id]);
});

it('defaults node moves to the primary site regardless of current site', function() {
    $secondary = F::secondarySite();
    $menu = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    $one = F::customNode($menu, 'One', '/one');
    $two = F::customNode($menu, 'Two', '/two');
    Craft::$app->getSites()->setCurrentSite($secondary);
    $result = ($this->tools)()->moveNode($two->id, 'before', $one->id);
    expect($result['node']['siteId'])->toBe($one->siteId);
});

it('rejects a move relative to itself without changing the order', function() {
    $menu = F::menu();
    $one = F::customNode($menu, 'One', '/one');
    $two = F::customNode($menu, 'Two', '/two');
    expect(fn() => ($this->tools)()->moveNode($two->id, 'before', $two->id))->toThrow(ToolCallException::class);
    expect(Node::find()->menuId($menu->id)->ids())->toBe([$one->id, $two->id]);
});

it('rejects a parent from a different menu through the element adapter', function() {
    $menu = F::menu();
    $other = F::customNode(F::menu(), 'Other', '/other');
    expect(fn() => ($this->adapter)()->create([
        'menuId' => $menu->id, 'type' => Custom::class, 'title' => 'Invalid', 'url' => '/invalid', 'parentId' => $other->id,
    ], [], $this->admin))->toThrow(ToolCallException::class);
    expect(Node::find()->menuId($menu->id)->count())->toBe(0);
});

it('uses Craft element tools for custom field discovery and the complete node lifecycle', function() {
    $menu = F::menu();
    $field = F::addPlainTextFieldToMenu($menu);
    $tools = Craft::$app->get('mcp')->getCapabilityContainer()->get(\craft\mcp\capabilities\Elements::class);
    $schema = $tools->fieldSchema('navigation-nodes', context: ['menuId' => $menu->id]);
    expect($schema['schema']['properties'])->toHaveKey($field->handle);
    $result = $tools->create('navigation-nodes', [
        'menuHandle' => $menu->handle, 'type' => Custom::class, 'title' => 'Lifecycle', 'url' => '/lifecycle',
    ], [$field->handle => 'Initial value']);
    $id = $result['element']['id'];
    expect($result['element'][$field->handle])->toBe('Initial value');
    $updated = $tools->update('navigation-nodes', id: $id, attributes: ['title' => 'Updated'], fields: [$field->handle => 'Edited value']);
    expect($updated['element']['title'])->toBe('Updated')
        ->and($updated['element'][$field->handle])->toBe('Edited value');
    expect($tools->delete('navigation-nodes', id: $id)['deleted'])->toBeTrue();
    expect(Node::find()->id($id)->one())->toBeNull();
    expect($tools->restore('navigation-nodes', id: $id)['restored'])->toBeTrue();
    expect(Node::find()->id($id)->one()->title)->toBe('Updated');
});

it('rejects unauthorized element writes and field schema discovery', function() {
    $menu = F::menu();
    $other = F::menu();
    $node = F::customNode($menu, 'Protected', '/protected');
    $editor = ($this->editor)($other);
    Craft::$app->getUser()->setIdentity($editor);
    $tools = Craft::$app->get('mcp')->getCapabilityContainer()->get(\craft\mcp\capabilities\Elements::class);
    expect(fn() => $tools->fieldSchema('navigation-nodes', context: ['menuId' => $menu->id]))->toThrow(ToolCallException::class);
    expect(fn() => $tools->create('navigation-nodes', ['menuId' => $menu->id, 'type' => Custom::class, 'url' => '/denied']))->toThrow(ToolCallException::class);
    expect(fn() => $tools->update('navigation-nodes', id: $node->id, attributes: ['title' => 'Denied']))->toThrow(ToolCallException::class);
    expect(fn() => $tools->delete('navigation-nodes', id: $node->id))->toThrow(ToolCallException::class);
    expect(Node::find()->id($node->id)->one()->title)->toBe('Protected');
});

it('keeps mutation tools out of read-only and public capability discovery', function() {
    $mcp = Craft::$app->get('mcp');
    $original = $mcp->tokenScopes;
    $discovery = new \craft\mcp\CapabilityDiscovery(Craft::$app->getConfig()->getGeneral(), $mcp->getCapabilityRegistry());
    try {
        $mcp->tokenScopes = [\craft\services\Mcp::SCOPE_READ];
        $tools = $discovery->discover(CRAFT_BASE_PATH, [])->getTools();
        expect($tools)->toHaveKeys(['navigation.menus.list', 'navigation.menus.tree'])
            ->not->toHaveKey('navigation.nodes.move');
        $mcp->tokenScopes = [\craft\services\Mcp::SCOPE_WRITE];
        $tools = $discovery->discover(CRAFT_BASE_PATH, [])->getTools();
        expect($tools)->toHaveKey('navigation.nodes.move');
        $move = $tools['navigation.nodes.move']->tool;
        expect($move->inputSchema['properties']['operation']['enum'])->toBe(['before', 'after', 'prepend', 'append']);
        $public = $discovery->discoverPublic(CRAFT_BASE_PATH, [])->getTools();
        expect($public)->not->toHaveKeys(['navigation.menus.list', 'navigation.menus.tree', 'navigation.nodes.move']);
    } finally {
        $mcp->tokenScopes = $original;
    }
});

it('refuses a move that carries another editors staged addition', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    $child = F::customNode($menu, 'Pending', '/pending', $parent);
    $target = F::customNode($menu, 'Target', '/target');
    $owner = ($this->editor)($menu);
    $child->setPendingPublish(true);
    $child->enabled = false;
    expect(Craft::$app->getElements()->saveElement($child))->toBeTrue();
    $sessions = \verbb\navigation\Navigation::$plugin->getBuildSessions();
    $sessions->stageAddedNodes($sessions->getOrCreate($menu->id, $child->siteId, $owner->id), [$child->id]);
    $before = \verbb\navigation\helpers\BuilderStructureRevision::get($menu);
    expect(fn() => ($this->tools)()->moveNode($parent->id, 'append', $target->id))->toThrow(ToolCallException::class);
    expect(\verbb\navigation\helpers\BuilderStructureRevision::get($menu))->toBe($before);
});

it('rejects reads and writes on a site outside the editors grants', function() {
    $secondary = F::secondarySite();
    $menu = F::menu();
    $node = F::customNode($menu, 'Shared', '/shared');
    Craft::$app->getUser()->setIdentity(($this->editor)($menu));
    expect(fn() => ($this->tools)()->menuTree($menu->handle, $secondary->id))->toThrow(ToolCallException::class);
    expect(fn() => ($this->tools)()->moveNode($node->id, 'prepend', siteId: $secondary->id))->toThrow(ToolCallException::class);
    expect(fn() => ($this->adapter)()->fieldSchemaElement(null, ['menuId' => $menu->id, 'siteId' => $secondary->id], Craft::$app->getUser()->getIdentity()))->toThrow(ToolCallException::class);
});

it('lists persisted dynamic nodes through a front-end MCP endpoint without projecting query rows', function() {
    $menu = F::menu();
    $section = F::entrySection();
    F::entries(2, $section);
    $dynamic = F::dynamicSectionNode($menu, $section);
    \Tests\Support\WebRequestSimulator::withAbsoluteUrl('https://boundary.invalid/mcp', function() use ($menu, $dynamic) {
        $tools = Craft::$app->get('mcp')->getCapabilityContainer()->get(\craft\mcp\capabilities\Elements::class);
        $result = $tools->list('navigation-nodes', ['menuId' => $menu->id, 'limit' => 1]);
        expect(array_column($result->structuredContent['elements'], 'id'))->toBe([$dynamic->id])
            ->and($result->structuredContent['nextOffset'])->toBeNull();
    });
});

it('reports a missing menu context as a tool error', function() {
    expect(fn() => ($this->adapter)()->fieldSchemaElement(null, [], $this->admin))->toThrow(ToolCallException::class);
});

it('requires the authorized move tool when updating an existing node parent', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    $child = F::customNode($menu, 'Pending', '/pending', $parent);
    $target = F::customNode($menu, 'Target', '/target');
    $owner = ($this->editor)($menu);
    $child->setPendingPublish(true);
    $child->enabled = false;
    expect(Craft::$app->getElements()->saveElement($child))->toBeTrue();
    $sessions = \verbb\navigation\Navigation::$plugin->getBuildSessions();
    $sessions->stageAddedNodes($sessions->getOrCreate($menu->id, $child->siteId, $owner->id), [$child->id]);
    $before = \verbb\navigation\helpers\BuilderStructureRevision::get($menu);
    expect(fn() => ($this->adapter)()->update($parent, ['parentId' => $target->id], [], $this->admin))->toThrow(ToolCallException::class);
    expect(\verbb\navigation\helpers\BuilderStructureRevision::get($menu))->toBe($before);
});
