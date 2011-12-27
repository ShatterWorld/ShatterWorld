<?php
namespace Rules\Quests;
use Rules\AbstractRule;
use Entities;

class FoodGathering extends AbstractQuest implements IQuest
{
	public function getDescription ()
	{
		return 'Zásoba jídla';
	}

	public function getExplanation (Entities\Quest $quest)
	{
		return 'Nashomáždi ' . $this->getTarget($quest) . ' jídla (' . $this->getStatus($quest) .' splněno)';
	}

	public function getValue ($level = 1)
	{
		return $level * 900;
	}

	public function getLevelCap ()
	{
		return 5;
	}
	public function getTarget (Entities\Quest $quest)
	{
		return 700 + pow($quest->level, 2) * 300;
	}

	public function getStatus (Entities\Quest $quest)
	{
		if (isset($this->status)){
			return $this->status;
		}
		$resources = $this->getContext()->model->resourceRepository->getResourcesArray($quest->owner);
		$status = floor($resources['food']['balance']);
		$this->status = $status;
		return $status;

	}
}
