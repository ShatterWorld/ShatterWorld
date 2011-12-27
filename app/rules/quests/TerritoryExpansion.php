<?php
namespace Rules\Quests;
use Rules\AbstractRule;
use Entities;

class TerritoryExpansion extends AbstractQuest implements IQuest
{
	public function getDescription ()
	{
		return 'Rozšíření území';
	}

	public function getExplanation (Entities\Quest $quest)
	{
		return 'Rozšiř území klanu na ' . $this->getTarget($quest) . ' polí (' . $this->getStatus($quest) . ' splněno)';
	}


	public function getValue ($level = 1)
	{
		return 800;
	}

	public function getLevelCap ()
	{
		return 10;
	}
	public function getTarget (Entities\Quest $quest)
	{
		return pow($quest->level, 2) + 6;
	}

	public function getStatus (Entities\Quest $quest)
	{
		if (isset($this->status)){
			return $this->status;
		}
		$status = $this->getContext()->model->fieldRepository->getTerritorySize($quest->owner);
		$this->status = $status;
		return $status;

	}
}
