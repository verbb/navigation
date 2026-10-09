<?php
namespace verbb\navigation\helpers;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;

use Craft;
use craft\helpers\UrlHelper;

use Throwable;

use Twig\Error\Error as TwigError;

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
    // Static Methods
    // =========================================================================

    /**
     * Renders an author object-template string in Base's always-on Twig sandbox.
     * Context should stay limited to safe scalars/arrays — never full User models
     * with privileged methods beyond what the sandbox already restricts.
     */
    public static function renderAuthorTemplate(string $template, array $object = []): string
    {
        if ($template === '' || !str_contains($template, '{')) {
            return $template;
        }

        // Craft supports ${VAR} environment syntax, but these author fields must
        // preserve it literally rather than resolve it or parse it as shorthand.
        $protected = [];
        $template = preg_replace_callback('/\$\{[A-Z_][A-Z0-9_]*\}/', function(array $matches) use (&$protected, $template): string {
            $placeholder = '__navigation_literal_env_' . hash('sha256', $template . "\0" . count($protected)) . '__';
            $protected[$placeholder] = $matches[0];

            return $placeholder;
        }, $template);

        try {
            $rendered = Navigation::$plugin->getTemplates()->renderSandboxedObjectTemplate($template, $object);
        } catch (Throwable $e) {
            // A stale or mistyped author token should fail closed without taking
            // down every front-end request that reads the affected menu.
            Craft::warning('Unable to render an author-entered Navigation template: ' . $e->getMessage(), __METHOD__);

            return '';
        }

        return $protected ? strtr($rendered, $protected) : $rendered;
    }

    /**
     * Renders a custom title translation key with the node data Craft exposes to
     * object templates, but without access to the unrestricted Twig environment.
     */
    public static function renderTranslationKeyTemplate(string $template, Node $node): string
    {
        try {
            // Translation keys are identifiers rather than markup, so preserve
            // Craft's historical unescaped object-template output.
            return Navigation::$plugin->getTemplates()->renderSandboxedObjectTemplate(
                $template,
                self::_translationKeyObject($template, $node),
                autoescape: false,
            );
        } catch (TwigError $e) {
            Craft::warning('Unable to render a Navigation title translation key: ' . $e->getMessage(), __METHOD__);

            return '';
        }
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

        // Browsers remove embedded ASCII tabs/newlines before parsing a scheme.
        // Reject controls rather than treating an unrecognized scheme as relative.
        if (preg_match('/[\x00-\x1f\x7f]/', $url)) {
            return null;
        }

        if (preg_match('/^([a-z][a-z0-9+.-]*):/i', $url, $matches)
            && !in_array(strtolower($matches[1]), self::ALLOWED_URL_SCHEMES, true)) {
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
        $siteUrl = UrlHelper::siteUrl('', null, null, $site->id);

        return [
            // Preserve the historical scalar token without exposing Craft's
            // broader template globals or the callable siteUrl() function.
            'siteUrl' => $siteUrl,
            'site' => [
                'id' => $site->id,
                'name' => $site->name,
                'handle' => $site->handle,
                'language' => $site->language,
                'baseUrl' => $siteUrl,
            ],
        ];
    }


    // Private Methods
    // =========================================================================

    /**
     * Preserves Craft's safe element and Node attribute tokens without passing
     * the Node model itself into the sandbox.
     */
    private static function _translationKeyObject(string $template, Node $node): array
    {
        $object = [
            'id' => $node->id,
            'uid' => $node->uid,
            'title' => $node->title,
            'slug' => $node->slug,
            'uri' => $node->uri,
            'siteId' => $node->siteId,
            'enabled' => $node->enabled,
            'dateCreated' => $node->dateCreated,
            'dateUpdated' => $node->dateUpdated,
            'elementId' => $node->elementId,
            'menuId' => $node->menuId,
            'type' => $node->type,
            'classes' => $node->classes,
            'urlSuffix' => $node->urlSuffix,
            'customAttributes' => $node->customAttributes,
            'data' => $node->data,
            'newWindow' => $node->newWindow,
            'deletedWithMenu' => $node->deletedWithMenu,
        ];

        if (preg_match('/\burl\b/', $template)) {
            $object['url'] = $node->getUrl();
        }

        if (preg_match('/\bsite\b/', $template)) {
            $object['site'] = $node->getSite();
        }

        if (preg_match('/\bstatus\b/', $template)) {
            $object['status'] = $node->getStatus();
        }

        foreach ($node->getFieldLayout()?->getCustomFields() ?? [] as $field) {
            if (preg_match('/\b' . preg_quote($field->handle, '/') . '\b/', $template)) {
                $object[$field->handle] = $node->getFieldValue($field->handle);
            }
        }

        return $object;
    }


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
}
