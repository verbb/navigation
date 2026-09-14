<?php
namespace verbb\navigation\elementactions;

use Craft;
use craft\elements\actions\Restore as CraftRestore;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Db;

class Restore extends CraftRestore
{
    // Public Methods
    // =========================================================================

    public function performAction(ElementQueryInterface $query): bool
    {
        $elementsService = Craft::$app->getElements();
        $user = Craft::$app->getUser()->getIdentity();
        $elements = [];

        foreach (Db::each($query) as $element) {
            if ($elementsService->canSave($element, $user)) {
                $elements[] = $element;
            }
        }

        // Restore together so parents are live before any node rebuilds its hierarchy.
        $success = $elements && $elementsService->restoreElements($elements);
        $this->setMessage($success ? $this->successMessage : $this->failMessage);

        return $success;
    }
}
