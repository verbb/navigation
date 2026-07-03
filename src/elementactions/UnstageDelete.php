<?php
namespace verbb\navigation\elementactions;

use verbb\navigation\elements\Node;
use verbb\navigation\Navigation;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Db;

class UnstageDelete extends ElementAction
{
    // Properties
    // =========================================================================

    private string $triggerId;


    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('navigation', 'Restore');
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
        return Craft::t('navigation', 'Restore');
    }

    public function getTriggerHtml(): ?string
    {
        Craft::$app->getView()->registerJsWithVars(fn($triggerId) => <<<JS
(() => {
    new Craft.ElementActionTrigger({
        triggerId: $triggerId,
        validateSelection: (selectedItems) => {
            for (let i = 0; i < selectedItems.length; i++) {
                if (!selectedItems.eq(i).find('.navigation-pending-status--delete').length) {
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
        $restoredCount = 0;

        foreach (Db::each($query) as $element) {
            if (!$element instanceof Node || !$element->getIsPendingDelete()) {
                continue;
            }

            if (!$elementsService->canView($element, $user)) {
                continue;
            }

            $session = $buildSessions->getOrCreate((int)$element->menuId, (int)$element->siteId);
            $buildSessions->unstageDelete($session, $element);
            $restoredCount++;
        }

        if ($restoredCount === 0) {
            Craft::$app->getSession()->setError(Craft::t('navigation', 'Couldn’t restore node.'));

            return false;
        }

        Craft::$app->getSession()->setNotice(Craft::t('navigation', 'Node{plural} restored to menu.', [
            'plural' => $restoredCount > 1 ? 's' : '',
        ]));

        return true;
    }
}
