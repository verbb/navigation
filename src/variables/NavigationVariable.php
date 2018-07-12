<?php
namespace verbb\navigation\variables;

use verbb\navigation\Navigation;
use verbb\navigation\elements\db\NodeQuery;
use verbb\navigation\elements\Node as NodeElement;

use Craft;
use craft\web\View;

use yii\base\Behavior;

class NavigationVariable
{
    public function getPluginName()
    {
        return Navigation::$plugin->getPluginName();
    }

    public function getRegisteredElements()
    {
        return Navigation::$plugin->elements->getRegisteredElements();
    }

    public function nodes($criteria = null): NodeQuery
    {
        $query = NodeElement::find();

        if ($criteria) {
            if (is_string($criteria)) {
                $criteria = ['handle' => $criteria];
            }

            Craft::configure($query, $criteria);
        }

        return $query;
    }

    public function render($criteria = null, array $options = [])
    {
        $nodes = $this->nodes($criteria)->all();

        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);

        echo Craft::$app->view->renderTemplate('navigation/_special/render', [
            'nodes' => $nodes,
            'options' => $options,
        ]);
    }
}
