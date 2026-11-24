<?php

use verbb\navigation\helpers\ProductTypeSettings;

// Commerce isn't installed in the test app, so these stubs stand in for the two shapes of
// Commerce's ProductTypes service: 5.6 and earlier (Craft 5.9) only have getEditableProductTypes(),
// and 5.7+ adds getViewableProductTypes() and deprecates the editable variant.

test('product type options fall back to editable product types before Commerce 5.7', function() {
    $productTypes = new class () {
        public function getEditableProductTypes(): array
        {
            return ['editable'];
        }
    };

    $method = new ReflectionMethod(ProductTypeSettings::class, '_getViewableProductTypes');

    expect($method->invoke(null, $productTypes))->toBe(['editable']);
});

test('product type options use viewable product types on Commerce 5.7+ without the deprecated call', function() {
    $productTypes = new class () {
        public function getViewableProductTypes(): array
        {
            return ['viewable'];
        }

        public function getEditableProductTypes(): array
        {
            throw new RuntimeException('The deprecated getEditableProductTypes() was called.');
        }
    };

    $method = new ReflectionMethod(ProductTypeSettings::class, '_getViewableProductTypes');

    expect($method->invoke(null, $productTypes))->toBe(['viewable']);
});
