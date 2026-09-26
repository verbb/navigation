<?php

use craft\elements\User;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\controllers\NodesController;
use verbb\navigation\elements\Node;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\Navigation as N;

it('does not expand server configuration in an editor-authored custom URL', function(string $url) {
    $menu = F::menu();
    $owner = new User(['username' => uniqid('url-owner-'), 'email' => uniqid('url-owner-') . '@example.test']);
    expect(Craft::$app->getElements()->saveElement($owner))->toBeTrue();
    $variable = 'NAVIGATION_AUDIT_URL_VALUE';
    $previous = $_SERVER[$variable] ?? null;
    $hadVariable = array_key_exists($variable, $_SERVER);
    $alias = '@navigationAuditUrlValue';
    $previousAlias = Craft::getAlias($alias, false);
    $sentinel = 'https://synthetic-value.example.test/private-audit-value';
    $_SERVER[$variable] = $sentinel;
    Craft::setAlias($alias, $sentinel);
    try {
        expect(craft\helpers\App::parseEnv($url))->toContain($sentinel);
        boundaryRequest(function() use ($menu, $owner, $url, $sentinel) {
            Craft::$app->set('userPermissions', new craft\services\UserPermissions());
            $site = Craft::$app->getSites()->getPrimarySite();
            expect(Craft::$app->getUserPermissions()->saveUserPermissions($owner->id, [
                'accessCp', 'editSite:' . $site->uid, 'navigation-manageMenu:' . $menu->uid,
            ]))->toBeTrue();
            Craft::$app->getUser()->setIdentity($owner);
            expect($owner->admin)->toBeFalse();
            $payload = boundaryAddPayload($menu, 'Author URL');
            $payload['url'] = $url;
            Craft::$app->getRequest()->setBodyParams(['nodes' => [$payload]]);
            $response = (new NodesController('nodes', N::$plugin))->actionAddNodes();
            expect($response->statusCode)->toBe(200);
            expect(Node::find()->menuId($menu->id)->status(null)->one()->getUrl())->toBe($url);
            $state = N::$plugin->getBuilderState()->getState($menu->id, $site->id);
            expect($state['nodes'])->toHaveCount(1);
            expect(json_encode($state['nodes'], JSON_UNESCAPED_SLASHES))->not->toContain($sentinel);
        });
    } finally {
        if ($hadVariable) {
            $_SERVER[$variable] = $previous;
        } else {
            unset($_SERVER[$variable]);
        }
        Craft::setAlias($alias, $previousAlias === false ? null : $previousAlias);
    }
})->with([
    '$NAVIGATION_AUDIT_URL_VALUE',
    'https://example.test/${NAVIGATION_AUDIT_URL_VALUE}',
    '@navigationAuditUrlValue',
]);

it('preserves literal custom URLs and the documented sandboxed site token', function() {
    foreach (['/contact', 'https://example.test/contact', 'mailto:hello@example.test', 'tel:+61355550100'] as $url) {
        $node = new Node(['type' => Custom::class, 'url' => $url]);
        expect($node->getUrl())->toBe($url);
    }
    $node = new Node(['type' => Custom::class, 'url' => '{site.baseUrl}contact']);
    expect($node->getUrl())->toBe(craft\helpers\UrlHelper::siteUrl('') . 'contact');
});
