<?php

use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\nodetypes\Custom;

function boundaryRequest(callable $callback): mixed
{
    return W::withAbsoluteUrl('https://boundary.invalid/', function() use ($callback) {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Craft::$app->request->headers->set('Accept', 'application/json');
        $user = Craft::$app->getUser();
        $identity = $user->getIdentity();
        $response = Craft::$app->getResponse();
        $sources = Craft::$app->getElementSources();
        $sourceProperty = new ReflectionProperty(craft\base\Element::class, 'sources');
        $nativeSources = $sourceProperty->getValue();
        $sourceProperty->setValue(null, []);
        // Sources are request-cached by Craft. Each simulated request must see
        // sections and the identity created since previous tests ran.
        Craft::$app->set('elementSources', new craft\services\ElementSources());
        $user->setIdentity(craft\elements\User::find()->admin()->one());
        Craft::$app->set('response', new craft\web\Response());
        try {
            return $callback();
        } finally {
            $user->setIdentity($identity);
            Craft::$app->set('response', $response);
            Craft::$app->set('elementSources', $sources);
            $sourceProperty->setValue(null, $nativeSources);
        }
    });
}

function boundaryAddPayload($menu, string $title): array
{
    return ['menuId' => $menu->id, 'siteId' => Craft::$app->sites->getPrimarySite()->id,
        'title' => $title, 'url' => '/boundary', 'type' => Custom::class];
}

