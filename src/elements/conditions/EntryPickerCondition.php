<?php
namespace verbb\navigation\elements\conditions;

use craft\base\ElementInterface;
use craft\elements\conditions\entries\EntryCondition;
use craft\elements\db\ElementQueryInterface;
use craft\elements\Entry;

class EntryPickerCondition extends EntryCondition
{
    // Properties
    // =========================================================================

    public array $hiddenSectionIds = [];


    // Public Methods
    // =========================================================================

    public function modifyQuery(ElementQueryInterface $query): void
    {
        parent::modifyQuery($query);

        if ($this->hiddenSectionIds) {
            // Preserve section restrictions already applied by the selected source.
            $query->andWhere(['not in', 'entries.sectionId', $this->hiddenSectionIds]);
        }
    }

    public function matchElement(ElementInterface $element): bool
    {
        return $element instanceof Entry
            && (!$this->hiddenSectionIds || ($element->sectionId !== null && !in_array($element->sectionId, $this->hiddenSectionIds)))
            && parent::matchElement($element);
    }


    // Protected Methods
    // =========================================================================

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            [['hiddenSectionIds'], 'safe'],
        ]);
    }

    protected function config(): array
    {
        return array_merge(parent::config(), [
            'hiddenSectionIds' => $this->hiddenSectionIds,
        ]);
    }
}
