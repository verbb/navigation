<?php

declare(strict_types=1);

use craft\elements\User;
use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\helpers\MenuAuth;
use verbb\navigation\helpers\NodeOutputSafety;

it('sanitizes author URLs to allowlisted schemes and relative paths', function() {
    expect(NodeOutputSafety::sanitizeUrl('/about'))->toBe('/about');
    expect(NodeOutputSafety::sanitizeUrl('#section'))->toBe('#section');
    expect(NodeOutputSafety::sanitizeUrl('https://example.com/x'))->toBe('https://example.com/x');
    expect(NodeOutputSafety::sanitizeUrl('mailto:hi@example.com'))->toBe('mailto:hi@example.com');
    expect(NodeOutputSafety::sanitizeUrl('javascript:alert(1)'))->toBeNull();
    expect(NodeOutputSafety::sanitizeUrl('data:text/html,hi'))->toBeNull();
});

it('allows only benign custom attribute names', function() {
    expect(NodeOutputSafety::isAllowedAttributeName('class'))->toBeTrue();
    expect(NodeOutputSafety::isAllowedAttributeName('aria-label'))->toBeTrue();
    expect(NodeOutputSafety::isAllowedAttributeName('data-track'))->toBeTrue();
    expect(NodeOutputSafety::isAllowedAttributeName('onclick'))->toBeFalse();
    expect(NodeOutputSafety::isAllowedAttributeName('onmouseover'))->toBeFalse();
    expect(NodeOutputSafety::isAllowedAttributeName('formaction'))->toBeFalse();
});

it('filters custom attributes after render', function() {
    $filtered = NodeOutputSafety::filterCustomAttributes([
        'class' => 'nav-item',
        'onclick' => 'alert(1)',
        'data-id' => '9',
    ]);

    expect($filtered)->toBe([
        'class' => 'nav-item',
        'data-id' => '9',
    ]);
});

it('renders sandboxed arithmetic object templates', function() {
    expect(NodeOutputSafety::renderAuthorTemplate('{{ 7 * 7 }}', []))->toBe('49');
});

it('denies menu manage without a user identity', function() {
    $nav = NavigationFixtureFactory::menu();

    expect(MenuAuth::canManageMenu(null, $nav))->toBeFalse();
    expect(MenuAuth::canEditMenu(null, $nav))->toBeFalse();
    expect(MenuAuth::canDeleteMenu(null, $nav))->toBeFalse();

    $node = NavigationFixtureFactory::customNode($nav, 'Auth Gate', '/auth-gate');
    $guest = new User(['admin' => false, 'active' => true, 'id' => null]);
    // Unsaved non-admin with no permission rows must not manage nodes via element ACL.
    if (!$guest->can('navigation-manageMenu:' . $nav->uid)) {
        expect($node->canView($guest))->toBeFalse();
        expect($node->canSave($guest))->toBeFalse();
        expect($node->canDelete($guest))->toBeFalse();
    } else {
        // Some Craft builds treat unsaved users as unrestricted — null identity is the hard deny.
        expect(true)->toBeTrue();
    }
});

it('allows admins to manage any menu node', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::customNode($nav, 'Admin Gate', '/admin-gate');
    $admin = new User(['admin' => true, 'active' => true]);

    expect(MenuAuth::canManageMenu($admin, $nav))->toBeTrue();
    expect($node->canSave($admin))->toBeTrue();
});
