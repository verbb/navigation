<?php
namespace verbb\navigation\services;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;

class Breadcrumbs extends Component
{
    // Public Methods
    // =========================================================================

    public function getBreadcrumbs(array $options = []): array
    {
        $limit = $options['limit'] ?? null;

        $breadcrumbs = [];

        // Add the homepage to the breadcrumbs
        if ($element = $this->_getPublicElementByUri('__home__')) {
            $breadcrumbs[] = $this->_getBreadcrumbItem($element, '');
        }

        $path = '';

        foreach (Craft::$app->getRequest()->getSegments() as $segment) {
            $path .= '/' . $segment;

            // Try and fetch an element based on the path
            $element = $this->_getPublicElementByUri(ltrim($path, '/'));

            if ($element) {
                $breadcrumbs[] = $this->_getBreadcrumbItem($element, $segment, $path);
            } else {
                $breadcrumbs[] = $this->_getBreadcrumbItem($segment, $segment, $path);
            }
        }

        if ($limit) {
            return array_slice($breadcrumbs, 0, $limit);
        }

        return $breadcrumbs;
    }


    // Private Methods
    // =========================================================================

    private function _getPublicElementByUri(string $uri): ?ElementInterface
    {
        $element = Craft::$app->getElements()->getElementByUri($uri, null, true);

        if (!$element) {
            return null;
        }

        $request = Craft::$app->getRequest();

        // Craft registers placeholder elements for signed preview requests so
        // templates can render unsaved content rather than the database state.
        if ($request->getIsPreview() || $request->getIsLivePreview()) {
            return $element;
        }

        // Element-type queries apply their normal public status rules, such as
        // Entry post/expiry dates and Commerce Product availability.
        return $element::find()
            ->id($element->id)
            ->siteId($element->siteId)
            ->one();
    }

    private function _getBreadcrumbItem($item, $segment, $path = ''): array
    {
        // Generate the title from the segment or element
        $title = StringHelper::titleize((string)$item);
        $isElement = false;
        $element = null;
        $elementId = null;
        $elementType = null;

        if ($item instanceof ElementInterface) {
            $isElement = true;
            $element = $item;
            $elementId = $item->id;
            $elementType = get_class($item);

            // Check if the element has titles setup
            if ($item->hasTitles()) {
                $title = $item->title;
            }
        }

        $url = UrlHelper::siteUrl($path);

        return [
            'title' => $title,
            'url' => $url,
            'segment' => $segment,
            'isElement' => $isElement,
            'element' => $element,
            'elementId' => $elementId,
            'elementType' => $elementType,
            'link' => Html::tag('a', Html::encode($title), ['href' => $url]),
        ];
    }
}
