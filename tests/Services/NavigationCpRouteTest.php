<?php

use Tests\Support\WebRequestSimulator;
use verbb\navigation\Navigation;
use verbb\navigation\controllers\MenusController;

it('uses the common plugin route as the primary control panel destination', function() {
    boundaryRequest(function() {
        $navItem = Navigation::$plugin->getCpNavItem();

        expect($navItem['url'])->toBe('navigation')
            ->and($navItem['subnav']['menus']['url'])->toBe('navigation');

        WebRequestSimulator::withAbsoluteUrl('https://boundary.invalid/navigation', function() {
            // Vite initialises the control panel asset manifest only for a CP request.
            Craft::$app->getRequest()->setIsCpRequest(true);
            // Craft reads the web session key when resolving a control panel site.
            $previousUser = Craft::$app->getUser();
            $user = new class extends craft\console\User {
                public string $idParam = '__id';
            };
            $user->setIdentity($previousUser->getIdentity());
            Craft::$app->set('user', $user);
            try {
                Craft::$app->getRequest()->setQueryParams([
                    'p' => 'navigation',
                    'site' => Craft::$app->getSites()->getPrimarySite()->handle,
                ]);
                $response = (new MenusController('menus', Navigation::$plugin))->actionIndex();

                expect($response->statusCode)->toBe(200)
                    ->and($response->headers->get('Location'))->toBeNull();
            } finally {
                Craft::$app->set('user', $previousUser);
            }
        });
    });
});

it('drops the path carrier from legacy control panel redirects', function() {
    boundaryRequest(function() {
        WebRequestSimulator::withAbsoluteUrl('https://boundary.invalid/navigation/navs', function() {
            Craft::$app->getRequest()->setQueryParams([
                'p' => 'navigation/navs',
                'site' => 'secondary',
                'source' => 'nav:main',
            ]);

            $response = (new MenusController('menus', Navigation::$plugin))->actionIndex();
            parse_str((string)parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);

            expect($response->statusCode)->toBe(302)
                ->and($query)->not->toHaveKey('p')
                ->and($query['site'])->toBe('secondary')
                ->and($query['source'])->toBe('menu:main');
        });
    });
});
