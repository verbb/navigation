<?php
namespace verbb\navigation\gql\queries;

use verbb\navigation\Navigation;
use verbb\navigation\deprecations\DeprecationHelper;
use verbb\navigation\elements\Menu;
use verbb\navigation\gql\types\generators\MenuGenerator;
use verbb\navigation\helpers\Gql as GqlHelper;
use verbb\navigation\models\MenuSettings;

use craft\gql\base\Query;

use GraphQL\Type\Definition\Type;
use GraphQL\Error\UserError;

class MenuQuery extends Query
{
    // Static Methods
    // =========================================================================

    public static function getQueries(bool $checkToken = true): array
    {
        if ($checkToken && !GqlHelper::canQueryNavigation()) {
            return [];
        }

        $queries = [];

        foreach (Navigation::$plugin->getMenus()->getAllMenus() as $nav) {
            // Match MenuGenerator: only expose menus the active schema may read.
            if ($checkToken && !GqlHelper::canQueryMenu($nav)) {
                continue;
            }

            $menu = Menu::find()->id($nav->id)->status(null)->one();

            if (!$menu) {
                continue;
            }

            $type = MenuGenerator::generateType($menu);

            $typeName = $menu->getGqlTypeName();

            $queries[$typeName] = [
                'type' => $type,
                'args' => [
                    'site' => [
                        'name' => 'site',
                        'type' => Type::string(),
                        'description' => 'The site handle.',
                    ],
                ],
                'resolve' => function($source, array $args) use ($menu, $nav) {
                    // Enforce per-menu scope when a schema is active (executeQuery / tokens).
                    // Direct resolver calls without a schema (unit tests) skip this gate.
                    try {
                        $schema = \Craft::$app->getGql()->getActiveSchema();
                    } catch (\Throwable) {
                        $schema = null;
                    }

                    if ($schema !== null && !GqlHelper::canQueryMenu($nav, $schema)) {
                        return null;
                    }

                    $query = Menu::find()->id($menu->id)->status(null);

                    if (!empty($args['site'])) {
                        $query->site($args['site']);
                    }

                    return $query->one();
                },
                'description' => 'Query a menu and its menu-level fields.',
            ];
        }

        $queries['navigationContext'] = [
            'type' => NavigationContextType::getType(),
            'args' => [
                'menuHandle' => Type::string(),
                'navHandle' => [
                    'type' => Type::string(),
                    'description' => 'Deprecated. Use `menuHandle` instead.',
                ],
            ],
            'resolve' => function($source, array $args) {
                $menuHandle = DeprecationHelper::resolveMenuHandleArg($args, 'navigationContext');

                if (!$menuHandle) {
                    throw new UserError('`menuHandle` is required.');
                }

                self::_requireMenuHandleAccess($menuHandle);

                return Navigation::$plugin->getContextResolver()->resolve($menuHandle);
            },
            'description' => 'Returns menu context helpers for the current request.',
        ];

        $queries['navigationMenuBreadcrumbs'] = [
            'type' => Type::listOf(MenuBreadcrumbType::getType()),
            'args' => [
                'menuHandle' => Type::string(),
                'navHandle' => [
                    'type' => Type::string(),
                    'description' => 'Deprecated. Use `menuHandle` instead.',
                ],
            ],
            'resolve' => function($source, array $args) {
                $menuHandle = DeprecationHelper::resolveMenuHandleArg($args, 'navigationMenuBreadcrumbs');

                if (!$menuHandle) {
                    throw new UserError('`menuHandle` is required.');
                }

                self::_requireMenuHandleAccess($menuHandle);

                return Navigation::$plugin->getMenuBreadcrumbs()->getBreadcrumbs($menuHandle);
            },
            'description' => 'Returns breadcrumbs through the menu tree for the current request.',
        ];

        return $queries;
    }


    // Private Methods
    // =========================================================================

    private static function _requireMenuHandleAccess(string $menuHandle): void
    {
        $nav = Navigation::$plugin->getMenus()->getMenuByHandle($menuHandle);

        if (!$nav instanceof MenuSettings || !GqlHelper::canQueryMenu($nav)) {
            throw new UserError('Menu not found or not authorized for this schema.');
        }
    }
}
