<?php
namespace Rules\Quests;
use Rules\AbstractRule;
use Entities;

class TerritoryExpansion extends AbstractRule implements IQuest
{
	public function getDescription ()
	{
		return 'Rozšíření území';
	}

	public function getExplanation (Entities\Quest $quest)
	{
		$territorySize = $this->getContext()->model->fieldRepository->getTerritorySize($quest->owner);
		return 'Rozšiř území klanu na ' . (pow($quest->level, 2) + 6) . ' polí (' . $territorySize . ' splněno)';
	}

	public function getDependencies ($level = 1)
	{
		return array();
	}

	public function isCompleted (Entities\Quest $quest, $term = null)
	{
		if ($this->getContext()->model->fieldRepository->getTerritorySize($quest->owner) >= pow($quest->level, 2) + 6) {
			return TRUE;
		}
		return FALSE;
	}

	public function processCompletion (Entities\Quest $quest)
	{

	}

	public function getValue ($level = 1)
	{
		return 800;
	}

	public function getLevelCap ()
	{
		return 10;
	}
}
