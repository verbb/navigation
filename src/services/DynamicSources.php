<?php
namespace verbb\navigation\services;

use verbb\navigation\base\DynamicSourceProvider;
use verbb\navigation\dynamic\sources\AssetVolumeDynamicSource;
use verbb\navigation\dynamic\sources\CategoryGroupDynamicSource;
use verbb\navigation\dynamic\sources\EntrySectionDynamicSource;
use verbb\navigation\dynamic\sources\ProductTypeDynamicSource;
use verbb\navigation\elements\Node;
use verbb\navigation\events\RegisterDynamicSourceEvent;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\elements\db\ElementQueryInterface;

class DynamicSources extends Component
{
    // Constants
    // =========================================================================

    public const EVENT_REGISTER_DYNAMIC_SOURCES = 'registerDynamicSources';


    // Properties
    // =========================================================================

    private ?array $_providerClasses = null;
    private bool $_includePendingProjections = false;


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        parent::init();

        $this->getRegisteredProviderClasses();
    }

    public function getRegisteredProviderClasses(): array
    {
        if ($this->_providerClasses !== null) {
            return $this->_providerClasses;
        }

        $providers = [
            EntrySectionDynamicSource::class,
            CategoryGroupDynamicSource::class,
            AssetVolumeDynamicSource::class,
        ];

        if (Craft::$app->getPlugins()->isPluginEnabled('commerce') && class_exists('craft\\commerce\\elements\\Product')) {
            $providers[] = ProductTypeDynamicSource::class;
        }

        $event = new RegisterDynamicSourceEvent([
            'providers' => $providers,
        ]);

        if ($this->hasEventHandlers(self::EVENT_REGISTER_DYNAMIC_SOURCES)) {
            $this->trigger(self::EVENT_REGISTER_DYNAMIC_SOURCES, $event);
        }

        $this->_providerClasses = array_values(array_unique($event->providers));

        return $this->_providerClasses;
    }

    public function getDefaultHandle(): string
    {
        $classes = $this->getRegisteredProviderClasses();

        return $classes === [] ? '' : $classes[0]::handle();
    }

    public function getProviderOptions(): array
    {
        $options = [[
            'label' => Craft::t('navigation', 'Select a source'),
            'value' => '',
        ]];

        foreach ($this->getRegisteredProviderClasses() as $class) {
            $options[] = [
                'label' => $class::displayName(),
                'value' => $class::handle(),
            ];
        }

        return $options;
    }

    public function getProviderClass(?string $handle): ?string
    {
        if (!$handle) {
            return null;
        }

        foreach ($this->getRegisteredProviderClasses() as $class) {
            if ($class::handle() === $handle) {
                return $class;
            }
        }

        return null;
    }

    public function getProviderClassForNode(Node $node): ?string
    {
        $handle = is_array($node->data) ? ($node->data['dynamicSource'] ?? null) : null;

        if (!$handle) {
            return null;
        }

        return $this->getProviderClass($handle);
    }

    /**
     * Opt into including disabled/pending sources in Dynamic projections for this request.
     * Public front-end reads must leave this false so live/public status stays the default.
     */
    public function includePendingProjections(bool $value = true): void
    {
        $this->_includePendingProjections = $value;
    }

    public function shouldIncludePendingProjections(): bool
    {
        return $this->_includePendingProjections;
    }

    /**
     * Apply pending visibility only when explicitly opted in; otherwise leave Craft defaults
     * (live/public) so projections never leak unpublished sources on the public site.
     */
    public function applyProjectionStatus(ElementQueryInterface $query): void
    {
        if ($this->_includePendingProjections) {
            $query->status(null);
        }
    }

    public function getProjectedChildren(Node $parent, int $siteId): array
    {
        $class = $this->getProviderClassForNode($parent);

        if (!$class) {
            return [];
        }

        return $class::getProjectedChildren($parent, $siteId);
    }

    public function getCacheTagsForNode(Node $node): array
    {
        $class = $this->getProviderClassForNode($node);

        if (!$class) {
            return [];
        }

        return $class::getCacheTags($node);
    }

    public function getCacheTagsForProjectedElement(ElementInterface $element): array
    {
        $tags = [];

        foreach ($this->getRegisteredProviderClasses() as $class) {
            if ($class::elementType() !== get_class($element)) {
                continue;
            }

            $tags = array_merge($tags, $class::getCacheTagsForProjectedElement($element));
        }

        return array_values(array_unique($tags));
    }

    public function shouldDeleteNodeOnSourceDelete(Node $node, string $sourceType, int $sourceId): bool
    {
        $class = $this->getProviderClassForNode($node);

        if (!$class) {
            return false;
        }

        return $class::shouldDeleteNodeOnSourceDelete($node, $sourceType, $sourceId);
    }
}
