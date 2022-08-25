<?php
namespace verbb\navigation\models;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\records\Nav as NavRecord;

use Craft;
use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
use craft\db\Table;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;

class Nav extends Model
{
    // Constants
    // =========================================================================
    
    public const DEFAULT_PLACEMENT_BEGINNING = 'beginning';
    public const DEFAULT_PLACEMENT_END = 'end';


    // Properties
    // =========================================================================

    public ?int $id = null;
    public ?int $structureId = null;
    public ?int $fieldLayoutId = null;
    public ?string $name = null;
    public ?string $handle = null;
    public ?string $instructions = null;
    public ?int $sortOrder = null;
    public bool $propagateNodes = false;
    public ?int $maxNodes = null;
    public ?int $maxLevels = null;
    public string $defaultPlacement = self::DEFAULT_PLACEMENT_END;
    public array $permissions = [];
    public ?string $uid = null;

    private array $_siteSettings;


    // Public Methods
    // =========================================================================

    public function __toString()
    {
        return Craft::t('site', $this->name) ?: static::class;
    }

    public function attributeLabels(): array
    {
        return [
            'handle' => Craft::t('app', 'Handle'),
            'name' => Craft::t('app', 'Name'),
        ];
    }

    protected function defineBehaviors(): array
    {
        return [
            'fieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => Node::class,
            ],
        ];
    }

    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['id', 'structureId', 'fieldLayoutId', 'maxLevels'], 'number', 'integerOnly' => true];
        $rules[] = [['handle'], HandleValidator::class, 'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']];
        $rules[] = [['handle'], UniqueValidator::class, 'targetClass' => NavRecord::class];
        $rules[] = [['name', 'handle', 'siteSettings'], 'required'];
        $rules[] = [['name', 'handle'], 'string', 'max' => 255];
        $rules[] = [['defaultPlacement'], 'in', 'range' => [self::DEFAULT_PLACEMENT_BEGINNING, self::DEFAULT_PLACEMENT_END]];
        $rules[] = [['fieldLayout'], 'validateFieldLayout'];
        $rules[] = [['siteSettings'], 'validateSiteSettings'];

        return $rules;
    }

    public function validateFieldLayout(): void
    {
        $fieldLayout = $this->getFieldLayout();

        $fieldLayout->reservedFieldHandles = [
            'nav',
        ];

        if (!$fieldLayout->validate()) {
            $this->addModelErrors($fieldLayout, 'fieldLayout');
        }
    }

    public function validateSiteSettings(): void
    {
        foreach ($this->getSiteSettings() as $i => $siteSettings) {
            if (!$siteSettings->validate()) {
                $this->addModelErrors($siteSettings, "siteSettings[$i]");
            }
        }
    }

    public function getSiteSettings(): array
    {
        if (isset($this->_siteSettings)) {
            return $this->_siteSettings;
        }

        if (!$this->id) {
            return [];
        }

        // Set them with setSiteSettings() so setNav() gets called on them
        $this->setSiteSettings(Navigation::$plugin->getNavs()->getNavSiteSettings($this->id));

        return $this->_siteSettings;
    }

    public function setSiteSettings(array $siteSettings): void
    {
        $this->_siteSettings = ArrayHelper::index($siteSettings, 'siteId');

        foreach ($this->_siteSettings as $settings) {
            $settings->setNav($this);
        }
    }

    public function getSites(): array
    {
        $sites = [];

        $sitesService = Craft::$app->getSites();

        foreach ($this->getSiteIds() as $siteId) {
            $sites[] = $sitesService->getSiteById($siteId);
        }

        return $sites;
    }

    public function getSiteIds(): array
    {
        $siteIds = [];

        foreach ($this->getSiteSettings() as $siteSetting) {
            if ($siteSetting->enabled) {
                $siteIds[] = $siteSetting->siteId;
            }
        }

        return $siteIds;
    }

    public function getConfig(): array
    {
        $config = [
            'name' => $this->name,
            'handle' => $this->handle,
            'structure' => [
                'uid' => $this->structureId ? Db::uidById(Table::STRUCTURES, $this->structureId) : StringHelper::UUID(),
                'maxLevels' => (int)$this->maxLevels ?: null,
            ],
            'instructions' => $this->instructions,
            'propagateNodes' => $this->propagateNodes,
            'maxNodes' => $this->maxNodes,
            'sortOrder' => (int)$this->sortOrder,
            'permissions' => $this->permissions,
            'siteSettings' => [],
            'defaultPlacement' => $this->defaultPlacement ?? self::DEFAULT_PLACEMENT_END,
        ];

        $fieldLayout = $this->getFieldLayout();

        if ($fieldLayoutConfig = $fieldLayout->getConfig()) {
            $config['fieldLayouts'] = [
                $fieldLayout->uid => $fieldLayoutConfig,
            ];
        }

        foreach ($this->getSiteSettings() as $siteId => $siteSettings) {
            $siteUid = Db::uidById(Table::SITES, $siteId);

            $config['siteSettings'][$siteUid] = [
                'enabled' => (bool)$siteSettings['enabled'],
            ];
        }

        return $config;
    }

}
