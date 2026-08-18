<?php
namespace verbb\navigation\models;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node as NodeElement;

use craft\base\ElementInterface;
use craft\base\Model;
use craft\helpers\Html;
use craft\helpers\Template;
use craft\helpers\UrlHelper;

use Twig\Markup;

class ProjectedNode extends Model
{
    // Static Methods
    // =========================================================================

    public static function fromEntry(ElementInterface $entry, NodeElement $parent, int $depth = 1): self
    {
        return self::fromElement($entry, $parent, $depth);
    }

    public static function fromElement(ElementInterface $element, NodeElement $parent, int $depth = 1): self
    {
        $level = max(1, (int)$parent->level) + $depth;

        $projected = new self([
            'id' => 'projected:' . $element->id,
            'uid' => 'projected:' . $element->uid,
            'title' => (string)($element->title ?: $element),
            'uri' => $element->uri ?? null,
            'url' => $element->url ?? null,
            'level' => $level,
            'siteId' => (int)$element->siteId,
            'elementId' => (int)$element->id,
            'parent' => $parent,
        ]);
        $projected->setElement($element);

        return $projected;
    }


    // Properties
    // =========================================================================

    public string $id;
    public string $uid;
    public string $title;
    public ?string $url = null;
    public ?string $uri = null;
    public int $level = 1;
    public int $lft = 0;
    public int $rgt = 0;
    public int $siteId;
    public ?int $elementId = null;
    public bool $isProjected = true;
    public ?NodeElement $parent = null;
    public array $children = [];

    private ?ElementInterface $_element = null;
    private ?bool $_isCurrent = null;
    private ?bool $_isActive = null;
    private bool $_hasActiveChild = false;
    private mixed $_prevElement = false;
    private mixed $_nextElement = false;


    // Public Methods
    // =========================================================================

    public function __toString(): string
    {
        return $this->title;
    }

    public function getIsProjected(): bool
    {
        return true;
    }

    public function setPrev(mixed $element): void
    {
        $this->_prevElement = $element;
    }

    public function setNext(mixed $element): void
    {
        $this->_nextElement = $element;
    }

    public function getElement(): ?ElementInterface
    {
        return $this->_element;
    }

    public function setElement(?ElementInterface $element): void
    {
        $this->_element = $element;
    }

    public function getUrl(): ?string
    {
        if ($this->url !== null) {
            return $this->url;
        }

        if ($this->uri !== null) {
            $path = ($this->uri === '__home__') ? '' : $this->uri;

            return UrlHelper::siteUrl($path, null, null, $this->siteId);
        }

        return null;
    }

    public function getCurrent(): bool
    {
        return $this->_isCurrent ?? Navigation::$plugin->getActiveMatcher()->isProjectedCurrent($this);
    }

    public function getActive(): bool
    {
        return $this->_isActive ?? Navigation::$plugin->getActiveMatcher()->isProjectedActive($this);
    }

    public function hasActiveChild(): bool
    {
        return $this->_hasActiveChild;
    }

    public function setActiveState(bool $isCurrent, bool $isActive, bool $hasActiveChild = false): void
    {
        $this->_isCurrent = $isCurrent;
        $this->_isActive = $isActive;
        $this->_hasActiveChild = $hasActiveChild;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getTag(): string
    {
        return $this->getUrl() ? 'a' : 'span';
    }

    public function getTarget(): string
    {
        return '';
    }

    public function getLinkAttributes(?array $extraAttributes = null): Markup
    {
        $attributes = array_merge([
            'href' => $this->getUrl(),
        ], $extraAttributes ?? []);

        $attributes = array_filter($attributes, static fn(mixed $value): bool => $value !== null && $value !== '');

        return Template::raw(Html::renderTagAttributes($attributes));
    }

    public function getLink(?array $attributes = null): ?Markup
    {
        $url = $this->getUrl();

        if (!$url) {
            return Template::raw(Html::encode($this->title));
        }

        return Template::raw(
            '<a ' . $this->getLinkAttributes($attributes) . '>' . Html::encode($this->title) . '</a>',
        );
    }
}
