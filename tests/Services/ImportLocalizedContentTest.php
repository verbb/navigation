<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\ImportExportHelper;

it('preserves shared node translations and site status when restoring a backup', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $field = F::addPlainTextFieldToMenu($menu);
    $field->translationMethod = craft\base\Field::TRANSLATION_METHOD_SITE;
    expect(Craft::$app->fields->saveField($field))->toBeTrue();
    $node = F::customNodeWithField($menu, $field, 'Primary field');
    $localized = Node::find()->id($node->id)->siteId($site->id)->one();
    $localized->title = 'Translated title';
    $localized->setEnabledForSite(false);
    $localized->setFieldValue($field->handle, 'Translated field');
    expect(Craft::$app->elements->saveElement($localized, true, false))->toBeTrue();
    $original = Node::find()->id($node->id)->siteId($site->id)->status(null)->one();
    expect($original->title)->toBe('Translated title');
    expect($original->getFieldValue($field->handle))->toBe('Translated field');
    expect($original->getEnabledForSite())->toBeFalse();

    $result = ImportExportHelper::importMenuFromJson(ImportExportHelper::generateMenuExport($menu), 'update');
    expect($result->errors)->toBe([]);
    $restored = Node::find()->menuId($menu->id)->siteId($site->id)->status(null)->one();
    expect($restored->title)->toBe($original->title);
    expect($restored->getFieldValue($field->handle))->toBe($original->getFieldValue($field->handle));
    expect($restored->getEnabledForSite())->toBeFalse();
    $primary = Node::find()->menuId($menu->id)->siteId(Craft::$app->sites->getPrimarySite()->id)->one();
    expect($primary->title)->toBe($node->title);
    expect($primary->getFieldValue($field->handle))->toBe('Primary field');
    expect($primary->getEnabledForSite())->toBeTrue();
});
