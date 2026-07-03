<?php

declare(strict_types=1);

namespace Tests\Support;

use Craft;
use craft\web\Request;

class WebRequestSimulator
{
    /**
     * Runs a callback with a simulated front-end web request URL.
     */
    public static function withAbsoluteUrl(string $absoluteUrl, callable $callback): mixed
    {
        $parsed = parse_url($absoluteUrl);
        $path = $parsed['path'] ?? '/';
        $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
        $host = $parsed['host'] ?? 'localhost';
        $scheme = $parsed['scheme'] ?? 'https';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';

        $previousServer = [
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
            'HTTPS' => $_SERVER['HTTPS'] ?? null,
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? null,
            'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? null,
        ];

        $_SERVER['HTTP_HOST'] = $host . $port;
        $_SERVER['HTTPS'] = $scheme === 'https' ? 'on' : 'off';
        $_SERVER['REQUEST_URI'] = $path . $query;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $previousRequest = Craft::$app->get('request', false);
        $general = Craft::$app->getConfig()->getGeneral();
        $previousOmitScriptName = $general->omitScriptNameInUrls;
        $general->omitScriptNameInUrls = true;

        /** @var Request $request */
        $request = Craft::createObject([
            'class' => Request::class,
            'isConsoleRequest' => false,
        ]);
        Craft::$app->set('request', $request);

        try {
            return $callback();
        } finally {
            Craft::$app->set('request', $previousRequest);
            $general->omitScriptNameInUrls = $previousOmitScriptName;

            foreach ($previousServer as $key => $value) {
                if ($value === null) {
                    unset($_SERVER[$key]);
                } else {
                    $_SERVER[$key] = $value;
                }
            }
        }
    }
}
