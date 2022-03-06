<?php
namespace verbb\navigation\models;

use verbb\navigation\elements\Node;
use verbb\navigation\records\Nav as NavRecord;

use Craft;
use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\models\FieldLayout;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;

class Nav extends Model
{
    // Properties
    // =========================================================================

    public ?int $id = null;
    public ?string $name = null;
    public ?string $handle = null;
    public ?string $instructions = null;
    public ?int $sortOrder = null;
    public bool $propagateNodes = false;
    public ?int $maxNodes = null;
    public ?int $maxLevels = null;
    public array $permissions = [];
    public array $siteSettings = [];
    public ?string $structureId = null;
    public ?int $fieldLayoutId = null;
    public ?string $uid = null;


    // Public Methods
    // =========================================================================

    public function __construct($config = [])
    {
        // Config normalization
        if (array_key_exists('permissions', $config)) {
            if (is_string($config['permissions'])) {
                $config['permissions'] = Json::decodeIfJson($config['permissions']);
            }

            if (!is_array($config['permissions'])) {
                unset($config['permissions']);
            }
        }

        if (array_key_exists('siteSettings', $config)) {
            if (is_string($config['siteSettings'])) {
                $config['siteSettings'] = Json::decodeIfJson($config['siteSettings']);
            }

            if (!is_array($config['siteSettings'])) {
                unset($config['siteSettings']);
            }
        }

        parent::__construct($config);
    }

    public function __toString()
    {
        return (string)$this->handle;
    }

    public function attributeLabels(): array
    {
        return [
            'handle' => Craft::t('app', 'Handle'),
            'name' => Craft::t('app', 'Name'),
        ];
    }

    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['id', 'structureId', 'maxLevels'], 'number', 'integerOnly' => true];
        $rules[] = [['handle'], HandleValidator::class, 'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']];
        $rules[] = [['handle'], UniqueValidator::class, 'targetClass' => NavRecord::class];
        $rules[] = [['name', 'handle'], 'required'];
        $rules[] = [['name', 'handle'], 'string', 'max' => 255];
        $rules[] = [['siteSettings'], 'validateSiteSettings', 'skipOnEmpty' => false];

        return $rules;
    }

    public function getNavFieldLayout(): ?FieldLayout
    {
        return $this->getBehavior('navFieldLayout')->getFieldLayout();
    }

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['navFieldLayout'] = [
            'class' => FieldLayoutBehavior::class,
            'elementType' => Node::class,
            'idAttribute' => 'fieldLayoutId',
        ];

        return $behaviors;
    }

    public function validateSiteSettings($attribute): void
    {
        if (!Craft::$app->getIsMultiSite()) {
            return;
        }

        if (empty($this->siteSettings)) {
            $this->addError($attribute, Craft::t('navigation', 'You must select at least one site.'));
        }
    }

    public function getEditableSites(): array
    {
        $sites = [];

        foreach (Craft::$app->getSites()->getEditableSites() as $site) {
            if (Craft::$app->getIsMultiSite()) {
                $enabled = $this->siteSettings[$site->uid]['enabled'] ?? false;

                // Backward compatibility, enabled if no settings yet
                if ($enabled || $this->siteSettings === null) {
                    $sites[] = $site;
                }
            } else {
                $sites[] = $site;
            }
        }

        return $sites;
    }

    public function getEditableSiteIds(): array
    {
        return ArrayHelper::getColumn($this->getEditableSites(), 'id');
    }

}
