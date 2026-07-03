<?php
namespace verbb\navigation\gql\queries;

use verbb\navigation\Navigation;
use verbb\navigation\deprecations\DeprecationHelper;
use verbb\navigation\elements\Menu;
use verbb\navigation\gql\types\generators\MenuGenerator;
use verbb\navigation\helpers\Gql as GqlHelper;
use verbb\navigation\models\NavigationContext;

use craft\gql\base\Query;
use craft\gql\GqlEntityRegistry;

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
                'resolve' => function($source, array $args) use ($menu) {
                    $query = Menu::find()->id($menu->id);

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

                return Navigation::$plugin->getMenuBreadcrumbs()->getBreadcrumbs($menuHandle);
            },
            'description' => 'Returns breadcrumbs through the menu tree for the current request.',
        ];

        return $queries;
    }
}
