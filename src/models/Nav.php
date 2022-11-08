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

    public const PROPAGATION_METHOD_NONE = 'none';
    public const PROPAGATION_METHOD_SITE_GROUP = 'siteGroup';
    public const PROPAGATION_METHOD_LANGUAGE = 'language';
    public const PROPAGATION_METHOD_ALL = 'all';


    // Properties
    // =========================================================================

    public ?int $id = null;
    public ?int $structureId = null;
    public ?int $fieldLayoutId = null;
    public ?string $name = null;
    public ?string $handle = null;
    public ?string $instructions = null;
    public ?int $sortOrder = null;
    public string $propagationMethod = self::PROPAGATION_METHOD_ALL;
    public ?int $maxNodes = null;
    public ?int $maxLevels = null;
    public array $maxNodesSettings = [];
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
        $rules[] = [['name', 'handle', 'propagationMethod', 'siteSettings'], 'required'];
        $rules[] = [['name', 'handle'], 'string', 'max' => 255];
        $rules[] = [['defaultPlacement'], 'in', 'range' => [self::DEFAULT_PLACEMENT_BEGINNING, self::DEFAULT_PLACEMENT_END]];
        $rules[] = [['fieldLayout'], 'validateFieldLayout'];
        $rules[] = [['siteSettings'], 'validateSiteSettings'];

        $rules[] = [
            ['propagationMethod'], 'in', 'range' => [
                self::PROPAGATION_METHOD_NONE,
                self::PROPAGATION_METHOD_SITE_GROUP,
                self::PROPAGATION_METHOD_LANGUAGE,
                self::PROPAGATION_METHOD_ALL,
            ],
        ];

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

    public function getHasMultiSiteNodes(): bool
    {
        return (
            Craft::$app->getIsMultiSite() &&
            count($this->getSiteSettings()) > 1 &&
            $this->propagationMethod !== self::PROPAGATION_METHOD_NONE
        );
    }

    public function isOverMaxNodes($node): bool
    {
        if ($this->maxNodes) {
            $nodesService = Navigation::$plugin->getNodes();

            // Get all saved nodes, and temp nodes we're trying to add
            $nodes = $nodesService->getNodesForNav($this->id, $node->siteId, true);
            $totalNodes = count($nodes);

            if ($totalNodes > $this->maxNodes) {
                return true;
            }
        }

        return false;
    }

    public function isOverMaxLevel($node): bool
    {
        if ($this->maxNodesSettings) {
            foreach ($this->maxNodesSettings as $maxNodesSetting) {
                $level = $maxNodesSetting['level'] ?? null;
                $max = $maxNodesSetting['max'] ?? null;

                if ($level !== null && $max !== null && $node->level) {
                    if ($node->level == $level) {
                        // Get all saved nodes for the nav, at this level to compare
                        $totalNodes = Node::find()
                            ->navId($this->id)
                            ->descendantOf($node->getParent())
                            ->descendantDist(1)
                            ->siteId($node->siteId)
                            ->level($level)
                            ->status(null)
                            ->count();

                        // Add in any temp nodes we're trying to add
                        $totalNodes += count(Navigation::$plugin->getNodes()->getTempNodes());

                        if ($totalNodes > $max) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function getConfig(): array
    {
        if ($this->maxNodesSettings) {
            $levels = [];

            // Normalize some settings
            foreach ($this->maxNodesSettings as $key => $maxNodesSetting) {
                $level = $maxNodesSetting['level'] ?? null;
                $max = $maxNodesSetting['max'] ?? null;

                if (!$level || !$max || in_array($level, $levels)) {
                    unset($this->maxNodesSettings[$key]);
                }

                $levels[] = $level;
            }

            $this->maxNodesSettings = array_values($this->maxNodesSettings);
        }

        $config = [
            'name' => $this->name,
            'handle' => $this->handle,
            'structure' => [
                'uid' => $this->structureId ? Db::uidById(Table::STRUCTURES, $this->structureId) : StringHelper::UUID(),
                'maxLevels' => (int)$this->maxLevels ?: null,
            ],
            'instructions' => $this->instructions,
            'propagationMethod' => $this->propagationMethod,
            'maxNodes' => $this->maxNodes,
            'maxNodesSettings' => $this->maxNodesSettings,
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
