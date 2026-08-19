<?php
namespace verbb\navigation\elementactions;

use verbb\navigation\elements\Node;
use verbb\navigation\Navigation;

use Craft;
use craft\base\ElementAction;
use craft\elements\actions\DeleteActionInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Db;

class StageDelete extends ElementAction implements DeleteActionInterface
{
    // Properties
    // =========================================================================

    public bool $withDescendants = false;

    private string $triggerId;


    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('app', 'Delete');
    }

    public static function isDestructive(): bool
    {
        return true;
    }


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        parent::init();
        $this->triggerId = sprintf('action-trigger-%s', mt_rand());
    }

    public function getTriggerId(): string
    {
        return $this->triggerId;
    }

    public function getTriggerLabel(): string
    {
        if ($this->withDescendants) {
            return Craft::t('app', 'Delete (with descendants)');
        }

        return Craft::t('app', 'Delete');
    }

    public function canHardDelete(): bool
    {
        return false;
    }

    public function setHardDelete(): void
    {
    }

    public function getConfirmationMessage(): ?string
    {
        return null;
    }

    public function getTriggerHtml(): ?string
    {
        Craft::$app->getView()->registerJsWithVars(fn($triggerId) => <<<JS
(() => {
    new Craft.ElementActionTrigger({
        triggerId: $triggerId,
        validateSelection: (selectedItems) => {
            for (let i = 0; i < selectedItems.length; i++) {
                const element = selectedItems.eq(i).find('.element');

                if (
                    selectedItems.eq(i).find('.navigation-pending-status--delete').length
                    || !Garnish.hasAttr(element, 'data-deletable')
                ) {
                    return false;
                }
            }

            return true;
        },
    });
})();
JS, [
            $this->getTriggerId(),
        ]);

        return null;
    }

    public function performAction(ElementQueryInterface $query): bool
    {
        $buildSessions = Navigation::$plugin->getBuildSessions();
        $elementsService = Craft::$app->getElements();
        $user = Craft::$app->getUser()->getIdentity();

        if ($this->withDescendants) {
            $query
                ->with([
                    [
                        'descendants',
                        [
                            'orderBy' => ['structureelements.lft' => SORT_DESC],
                            'status' => null,
                        ],
                    ],
                ])
                ->orderBy(['structureelements.lft' => SORT_DESC]);
        }

        $menuId = null;
        $siteId = null;
        $stagedCount = 0;

        foreach (Db::each($query) as $element) {
            if (!$element instanceof Node) {
                continue;
            }

            if (!$elementsService->canView($element, $user) || !$elementsService->canDelete($element, $user)) {
                continue;
            }

            if ($element->getIsPendingDelete()) {
                continue;
            }

            $menuId ??= (int)$element->menuId;
            $siteId ??= (int)$element->siteId;

            $session = $buildSessions->getOrCreate($menuId, $siteId);
            $buildSessions->stageDelete($session, $element, false);
            $stagedCount++;

            if ($this->withDescendants) {
                foreach ($element->getDescendants()->all() as $descendant) {
                    if (
                        $descendant instanceof Node
                        && $elementsService->canView($descendant, $user)
                        && $elementsService->canDelete($descendant, $user)
                    ) {
                        $buildSessions->stageDelete($session, $descendant, false);
                        $stagedCount++;
                    }
                }
            }
        }

        if ($stagedCount === 0) {
            Craft::$app->getSession()->setError(Craft::t('navigation', 'Couldn’t stage node for deletion.'));

            return false;
        }

        return true;
    }
}
