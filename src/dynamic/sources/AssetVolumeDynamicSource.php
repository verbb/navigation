<?php
namespace verbb\navigation\dynamic\sources;

use verbb\navigation\base\DynamicSourceProvider;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\AssetVolumeSettings;
use verbb\navigation\helpers\DynamicSourceTypes;
use verbb\navigation\helpers\NodeTypeSchemaFields;
use verbb\navigation\models\ProjectedNode;
use verbb\navigation\Navigation;

use Craft;
use craft\base\ElementInterface;
use craft\elements\Asset as AssetElement;

class AssetVolumeDynamicSource implements DynamicSourceProvider
{
    // Static Methods
    // =========================================================================

    public static function handle(): string
    {
        return 'assetVolume';
    }

    public static function displayName(): string
    {
        return Craft::t('navigation', 'Asset volume');
    }

    public static function elementType(): string
    {
        return AssetElement::class;
    }

    public static function getAddNodeSchema(array $context): array
    {
        return [
            NodeTypeSchemaFields::volumeIdField(),
        ];
    }

    public static function getAddNodeDefaultData(): array
    {
        return ['volumeId' => ''];
    }

    public static function renderSlideoutHtml(Node $node): string
    {
        return AssetVolumeSettings::renderSlideoutHtml($node);
    }

    public static function applyPostData(Node $node): void
    {
        AssetVolumeSettings::applyPostData($node);
    }

    public static function validateNode(Node $node): bool
    {
        if (!AssetVolumeSettings::getVolumeFromNode($node)) {
            $node->addError('data', Craft::t('navigation', 'Please select a volume.'));

            return false;
        }

        return true;
    }

    public static function getDefaultTitle(Node $node): ?string
    {
        return AssetVolumeSettings::getVolumeFromNode($node)?->name;
    }

    public static function getTypeLabel(Node $node): ?string
    {
        return AssetVolumeSettings::getVolumeFromNode($node)?->name;
    }

    public static function getProjectedChildren(Node $parent, int $siteId): array
    {
        $settings = is_array($parent->data) ? $parent->data : [];
        $volumeId = (int)($settings['volumeId'] ?? 0);

        if (!$volumeId) {
            return [];
        }

        $query = AssetElement::find()
            ->volumeId($volumeId)
            ->status(null);

        AssetVolumeSettings::applyToQuery($query, $parent);

        return array_map(
            static fn(AssetElement $asset): ProjectedNode => ProjectedNode::fromElement($asset, $parent),
            $query->all(),
        );
    }

    public static function getCacheTags(Node $node): array
    {
        $volume = AssetVolumeSettings::getVolumeFromNode($node);

        if (!$volume?->uid) {
            return [];
        }

        return [Navigation::$plugin->getNavigationCache()->volumeTag($volume->uid)];
    }

    public static function getCacheTagsForProjectedElement(ElementInterface $element): array
    {
        if (!$element instanceof AssetElement) {
            return [];
        }

        $volume = Craft::$app->getVolumes()->getVolumeById((int)$element->volumeId);

        if (!$volume?->uid) {
            return [];
        }

        return [Navigation::$plugin->getNavigationCache()->volumeTag($volume->uid)];
    }

    public static function shouldDeleteNodeOnSourceDelete(Node $node, string $sourceType, int $sourceId): bool
    {
        return $sourceType === DynamicSourceTypes::VOLUME
            && (int)($node->data['volumeId'] ?? 0) === $sourceId;
    }
}
