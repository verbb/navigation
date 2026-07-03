<?php
namespace verbb\navigation\gql\types\generators;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Menu;
use verbb\navigation\gql\types\MenuType;
use verbb\navigation\helpers\Gql as NavigationGqlHelper;

use Craft;
use craft\gql\base\Generator;
use craft\gql\base\GeneratorInterface;
use craft\gql\base\SingleGeneratorInterface;
use craft\gql\GqlEntityRegistry;

use GraphQL\Type\Definition\Type;

class MenuGenerator extends Generator implements GeneratorInterface, SingleGeneratorInterface
{
    // Static Methods
    // =========================================================================

    public static function generateTypes(mixed $context = null): array
    {
        $gqlTypes = [];

        foreach (Navigation::$plugin->getMenus()->getAllMenus() as $nav) {
            $menu = Menu::find()->id($nav->id)->status(null)->one();

            if (!$menu) {
                continue;
            }

            $requiredContexts = Menu::gqlScopesByContext($menu);

            if (!NavigationGqlHelper::isSchemaAwareOf($requiredContexts) && !NavigationGqlHelper::canSchema('navigationMenus.all') && !NavigationGqlHelper::canSchema('navigationNavs.all')) {
                continue;
            }

            $type = static::generateType($menu);
            $gqlTypes[$type->name] = $type;
        }

        return $gqlTypes;
    }

    public static function generateType(mixed $context): mixed
    {
        $typeName = Menu::gqlTypeNameByContext($context);

        if ($createdType = GqlEntityRegistry::getEntity($typeName)) {
            return $createdType;
        }

        $contentFieldGqlTypes = self::getContentFields($context);
        $menuFields = Craft::$app->getGql()->prepareFieldDefinitions($contentFieldGqlTypes, $typeName);

        return GqlEntityRegistry::createEntity($typeName, new MenuType([
            'name' => $typeName,
            'fields' => function() use ($menuFields) {
                return array_merge([
                    'handle' => [
                        'name' => 'handle',
                        'type' => Type::string(),
                        'description' => 'The menu handle.',
                    ],
                    'title' => [
                        'name' => 'title',
                        'type' => Type::string(),
                        'description' => 'The menu name.',
                    ],
                ], $menuFields);
            },
        ]));
    }
}
