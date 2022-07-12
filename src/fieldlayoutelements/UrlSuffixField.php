<?php
namespace verbb\navigation\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\TextField;

class UrlSuffixField extends TextField
{
    // Properties
    // =========================================================================

    public string $attribute = 'urlSuffix';
    public bool $requirable = true;


    // Public Methods
    // =========================================================================

    public function defaultLabel(?ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('navigation', 'URL Suffix');
    }

    public function instructions(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('navigation', 'Additional content appended to the element‘s URL.');
    }
}
