<?php
namespace verbb\navigation\elements;

use verbb\navigation\Navigation;
use verbb\navigation\deprecations\MenuDeprecations;
use verbb\navigation\elements\db\MenuQuery;
use verbb\navigation\models\MenuSettings;

use Craft;
use craft\base\Element;
use craft\models\FieldLayout;

class Menu extends Element
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('navigation', 'Menu');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('navigation', 'Menus');
    }

    public static function refHandle(): ?string
    {
        return 'menu';
    }

    public static function hasUris(): bool
    {
        return false;
    }

    public static function isLocalized(): bool
    {
        return true;
    }

    public static function hasStatuses(): bool
    {
        return false;
    }

    public static function trackChanges(): bool
    {
        return true;
    }

    public static function find(): MenuQuery
    {
        return new MenuQuery(static::class);
    }

    public static function gqlTypeNameByContext(mixed $context): string
    {
        return $context->handle . '_Menu';
    }

    public static function gqlScopesByContext(mixed $context): array
    {
        return ['navigationMenus.' . ($context->menuUid ?? $context->uid)];
    }


    // Traits
    // =========================================================================

    use MenuDeprecations;


    // Properties
    // =========================================================================
    
    public ?int $structureId = null;
    public ?int $nodeFieldLayoutId = null;
    public ?int $menuFieldLayoutId = null;
    public ?string $handle = null;
    public ?string $instructions = null;
    public ?int $sortOrder = null;
    public string $propagationMethod = MenuSettings::PROPAGATION_METHOD_ALL;
    public ?int $maxNodes = null;
    public array $maxNodesSettings = [];
    public array $permissions = [];
    public string $defaultPlacement = MenuSettings::DEFAULT_PLACEMENT_END;
    public bool $showSiteMenu = true;
    public ?string $menuUid = null;


    // Public Methods
    // =========================================================================

    public function getMenuHandle(): string
    {
        return (string)$this->handle;
    }

    public function getNodeFieldLayout(): ?FieldLayout
    {
        return $this->_getMenuSettings()->getFieldLayout();
    }

    public function getFieldLayout(): ?FieldLayout
    {
        if (!$this->menuFieldLayoutId) {
            return null;
        }

        return Craft::$app->getFields()->getLayoutById($this->menuFieldLayoutId);
    }

    public function getGqlTypeName(): string
    {
        return static::gqlTypeNameByContext($this);
    }

    public function afterSave(bool $isNew): void
    {
        if ($this->handle) {
            Navigation::$plugin->getNavigationCache()->invalidateByHandle($this->handle);
        }

        parent::afterSave($isNew);
    }


    // Protected Methods
    // =========================================================================

    protected function _getMenuSettings(): MenuSettings
    {
        $nav = Navigation::$plugin->getMenus()->getMenuById($this->id);

        if (!$nav) {
            throw new \yii\base\InvalidConfigException('Invalid menu ID: ' . $this->id);
        }

        return $nav;
    }
}
