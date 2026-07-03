<?php
namespace verbb\navigation\variables;

use verbb\navigation\Navigation;
use verbb\navigation\deprecations\NavigationVariableDeprecations;
use verbb\navigation\elements\db\NodeQuery;
use verbb\navigation\elements\Menu;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\migrations\plugins\MigrationResult;
use verbb\navigation\models\NavigationContext;
use verbb\navigation\models\ProjectedNode;

use Craft;
use craft\helpers\Template;
use craft\web\View;

use Twig\Markup;

class NavigationVariable
{
    // Traits
    // =========================================================================

    use NavigationVariableDeprecations;


    // Public Methods
    // =========================================================================

    public function getPluginName(): string
    {
        return Navigation::$plugin->getPluginName();
    }

    public function getRegisteredNodeTypes(): array
    {
        return Navigation::$plugin->getNodeTypes()->getRegisteredNodeTypes();
    }

    public function menu($handle): \verbb\navigation\elements\db\MenuQuery
    {
        return Menu::find()->handle($handle);
    }

    public function nodes($criteria = null): NodeQuery
    {
        if ($criteria instanceof NodeQuery) {
            $query = $criteria;
        } else {
            $query = NodeElement::find();
        }

        if ($criteria) {
            if (is_string($criteria)) {
                $criteria = ['menuHandle' => $criteria];
            } elseif (is_array($criteria)) {
                $criteria = $this->normalizeDeprecatedNodeCriteria($criteria);
            }

            Craft::configure($query, $criteria);
        }

        return $query;
    }

    public function render($criteria = null, array $options = []): Markup
    {
        $nodes = $this->nodes($criteria)->all();

        $template = Craft::$app->getView()->renderTemplate('navigation/_special/render', [
            'nodes' => $nodes,
            'options' => $options,
        ], View::TEMPLATE_MODE_CP);

        return Template::raw($template);
    }

    /**
     * URL-segment breadcrumbs (Craft element URIs along the request path).
     */
    public function urlBreadcrumbs(array $options = []): array
    {
        return Navigation::$plugin->getBreadcrumbs()->getBreadcrumbs($options);
    }

    public function menuBreadcrumbs(string $menuHandle): array
    {
        return Navigation::$plugin->getMenuBreadcrumbs()->getBreadcrumbs($menuHandle);
    }

    public function context(string $menuHandle, mixed $criteria = null): NavigationContext
    {
        return Navigation::$plugin->getContextResolver()->resolve($menuHandle, $criteria);
    }

    public function tree($criteria = null, array $options = []): array
    {
        $includeLinkedElements = (bool)($options['withLinkedElements'] ?? false);

        return Navigation::$plugin->getNodeRead()->buildNodeTree(
            $this->nodes($criteria)->withNodeHierarchy(true)->all(),
            $includeLinkedElements,
            true,
        );
    }

    public function getActiveNode($criteria = null, $includeChildren = false): NodeElement|ProjectedNode|null
    {
        return Navigation::$plugin->getActiveMatcher()->findActiveNode(
            $this->nodes($criteria)->withNodeHierarchy()->all(),
            $includeChildren,
        );
    }

    public function getActiveNodes($criteria = null): array
    {
        return Navigation::$plugin->getActiveMatcher()->findActiveNodes(
            $this->nodes($criteria)->withNodeHierarchy()->all(),
        );
    }

    public function getCurrentNodes($criteria = null): array
    {
        return Navigation::$plugin->getActiveMatcher()->findCurrentNodes(
            $this->nodes($criteria)->withNodeHierarchy()->all(),
        );
    }

    public function getMenuByHandle($handle): ?Menu
    {
        return Menu::find()->handle($handle)->one();
    }

    public function getMenuById($id): ?Menu
    {
        return Menu::find()->id($id)->one();
    }

    public function getAllMenus(): array
    {
        return Menu::find()->all();
    }

    public function invalidateCache(?string $handle = null): void
    {
        Navigation::$plugin->getNavigationCache()->invalidateByHandle($handle);
    }

    public function getBuilderTabs($nav): array
    {
        return Navigation::$plugin->getMenus()->getBuilderTabs($nav);
    }

    public function getSettingsNavItems(): array
    {
        $navItems = [
            'general' => ['title' => Craft::t('navigation', 'General Settings')],
            'performance' => ['title' => Craft::t('navigation', 'Performance')],
            'import-export' => ['title' => Craft::t('navigation', 'Import/Export')],
        ];

        $migrations = [];

        foreach (Navigation::$plugin->getMigrations()->getSources() as $sourceId => $source) {
            if (!($source['ready'] ?? false)) {
                continue;
            }

            $migrations['migrate/' . $sourceId] = ['title' => $source['label']];
        }

        if ($migrations !== []) {
            $navItems['migrations-heading'] = ['heading' => Craft::t('navigation', 'Migrations')];
            $navItems = array_merge($navItems, $migrations);
        }

        return $navItems;
    }

    public function renderMigrationOutput(array $lines): Markup
    {
        return Template::raw(MigrationResult::renderLinesHtml($lines));
    }
}
