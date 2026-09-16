<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Menu;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\ImportExportHelper as I;
use verbb\navigation\Navigation;

class ImportRollbackRejectNode extends verbb\navigation\nodetypes\Custom
{
    public function beforeSaveNode(bool $isNew): bool { return false; }
}

it('preserves custom field reads exports and later saves after an import rolls back', function() {
    $menu = F::menu();
    $field = F::addPlainTextFieldToMenu($menu);
    $layout = craft\models\FieldLayout::createFromConfig($menu->getFieldLayout()->getConfig());
    $layout->type = Menu::class;
    $layout->resetUids();
    $menu->setMenuFieldLayout($layout);
    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    $menu = Navigation::$plugin->getMenus()->getMenuById($menu->id);
    $node = F::customNodeWithField($menu, $field, 'Original badge');
    $content = Menu::find()->id($menu->id)->one();
    $content->setFieldValue($field->handle, 'Original menu content');
    expect(Craft::$app->elements->saveElement($content))->toBeTrue();
    $payload = I::generateMenuExport($menu);
    $fields = Craft::$app->getFields();
    $layouts = (new craft\db\Query())->from('{{%fieldlayouts}}')->orderBy('id')->all();
    $config = Craft::$app->projectConfig->get('navigation');
    $rejectedPayload = $payload;
    $rejectedPayload['nodes'][0]['type'] = ImportRollbackRejectNode::class;
    $register = static function(verbb\navigation\events\RegisterNodeTypeEvent $event) {
        $event->types[] = ImportRollbackRejectNode::class;
    };
    yii\base\Event::on(verbb\navigation\services\NodeTypes::class, verbb\navigation\services\NodeTypes::EVENT_REGISTER_NODE_TYPES, $register);
    try {
        $result = I::importMenuFromJson($rejectedPayload, 'update');
    } finally {
        yii\base\Event::off(verbb\navigation\services\NodeTypes::class, verbb\navigation\services\NodeTypes::EVENT_REGISTER_NODE_TYPES, $register);
    }
    expect($result->hasImportErrors())->toBeTrue();
    expect(Craft::$app->getFields())->toBe($fields);
    expect((new craft\db\Query())->from('{{%fieldlayouts}}')->orderBy('id')->all())->toBe($layouts);
    expect(Craft::$app->projectConfig->get('navigation'))->toBe($config);
    $restored = Node::find()->id($node->id)->one();
    expect($restored->getFieldValue($field->handle))->toBe('Original badge');
    expect(Menu::find()->id($menu->id)->one()->getFieldValue($field->handle))->toBe('Original menu content');
    $after = I::generateMenuExport($result->menu);
    expect($after['nodes'][0]['fieldValues'][$field->handle])->toBe('Original badge');
    expect($after['menuFieldValues'])->toBe($payload['menuFieldValues']);
    $restored->title = 'Edited after failed import';
    expect(Craft::$app->elements->saveElement($restored))->toBeTrue();
    expect(Node::find()->id($node->id)->one()->getFieldValue($field->handle))->toBe('Original badge');
    $retry = I::importMenuFromJson($payload, 'update');
    expect($retry->errors)->toBe([]);
    expect(Node::find()->menuId($menu->id)->one()->getFieldValue($field->handle))->toBe('Original badge');
    expect(Menu::find()->id($menu->id)->one()->getFieldValue($field->handle))->toBe('Original menu content');
});
