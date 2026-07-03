<?php
namespace verbb\navigation\deprecations;

use Craft;
use craft\helpers\UrlHelper;

use yii\web\Response;

trait MenusControllerDeprecations
{
    // Private Methods
    // =========================================================================

    private function _redirectLegacyNavsUrl(string $menusPath): ?Response
    {
        $path = Craft::$app->getRequest()->getPathInfo();

        if (!str_contains($path, 'navigation/navs')) {
            return null;
        }

        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(
            'navigation.cp.routes.navs',
            'CP routes under `navigation/navs/*` have been deprecated. Use `navigation/menus/*` instead.',
        );

        $query = Craft::$app->getRequest()->getQueryParams();

        if (!empty($query['source']) && str_starts_with((string)$query['source'], 'nav:')) {
            $query['source'] = 'menu:' . substr((string)$query['source'], 4);
        }

        $url = UrlHelper::cpUrl($menusPath, $query);

        return $this->redirect($url);
    }
}
