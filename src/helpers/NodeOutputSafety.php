<?php
namespace verbb\navigation\helpers;

use Craft;
use craft\helpers\UrlHelper;

/**
 * Author-field output policy for front-end markup.
 *
 * Custom URL / class / attribute values may contain `{…}` Twig. That is useful,
 * but menu authors are not template authors — render sandboxed, then enforce
 * URL schemes and attribute-name allowlists so event handlers and executable
 * schemes cannot reach the markup.
 */
class NodeOutputSafety
{
    // Constants
    // =========================================================================

    private const ALLOWED_URL_SCHEMES = [
        'http',
        'https',
        'mailto',
        'tel',
    ];

    private const ALLOWED_ATTRIBUTE_NAMES = [
        'accesskey',
        'class',
        'dir',
        'id',
        'lang',
        'rel',
        'role',
        'tabindex',
        'title',
    ];


    // Static Methods
    // =========================================================================

    /**
     * Renders an author object-template string in Craft's Twig sandbox.
     * Context should stay limited to safe scalars/arrays — never full User models
     * with privileged methods beyond what the sandbox already restricts.
     */
    public static function renderAuthorTemplate(string $template, array $object = []): string
    {
        if ($template === '' || !str_contains($template, '{')) {
            return $template;
        }

        return Craft::$app->getView()->renderSandboxedObjectTemplate($template, $object);
    }

    /**
     * Returns the URL when the scheme is allowlisted or the value is relative;
     * otherwise null so callers omit the href rather than emit javascript: etc.
     */
    public static function sanitizeUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return '';
        }

        // Relative / fragment / query-only destinations stay as authored.
        if (
            str_starts_with($url, '/') ||
            str_starts_with($url, '#') ||
            str_starts_with($url, '?') ||
            str_starts_with($url, './') ||
            str_starts_with($url, '../') ||
            !preg_match('/^[a-z][a-z0-9+.-]*:/i', $url)
        ) {
            return $url;
        }

        // Protocol-relative URLs are treated as https destinations.
        if (str_starts_with($url, '//')) {
            return $url;
        }

        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));

        if ($scheme === '' || !in_array($scheme, self::ALLOWED_URL_SCHEMES, true)) {
            return null;
        }

        return $url;
    }

    public static function isAllowedAttributeName(string $name): bool
    {
        $name = strtolower(trim($name));

        if ($name === '' || !preg_match('/^[a-z_][a-z0-9_.:-]*$/', $name)) {
            return false;
        }

        // Event-handler / executable attribute names are never allowed.
        if (str_starts_with($name, 'on')) {
            return false;
        }

        if (in_array($name, self::ALLOWED_ATTRIBUTE_NAMES, true)) {
            return true;
        }

        return str_starts_with($name, 'aria-') || str_starts_with($name, 'data-');
    }

    /**
     * Drops disallowed attribute names after author templates are rendered.
     * Built-in link attributes (href, target, rel, class) are already applied
     * by the caller; this filters the customAttributes map.
     */
    public static function filterCustomAttributes(array $attributes): array
    {
        $filtered = [];

        foreach ($attributes as $name => $value) {
            if (!is_string($name) || !self::isAllowedAttributeName($name)) {
                continue;
            }

            $filtered[$name] = $value;
        }

        return $filtered;
    }

    /**
     * Safe interpolation context for author templates (no User element identity).
     */
    public static function authorTemplateObject(): array
    {
        $site = Craft::$app->getSites()->getCurrentSite();

        return [
            'site' => [
                'id' => $site->id,
                'name' => $site->name,
                'handle' => $site->handle,
                'language' => $site->language,
                'baseUrl' => UrlHelper::siteUrl('', null, null, $site->id),
            ],
        ];
    }
}
