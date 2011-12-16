<?php
namespace Rules\Quests;
use Rules\AbstractRule;
use Entities;

class FoodGathering extends AbstractRule implements IQuest
{
	public function getDescription ()
	{
		return 'Zásoba jídla';
	}

	public function getExplanation ($level = 1)
	{
		return 'Nashomáždi ' . pow($level, 2) * 300 . ' jídla';
	}

	public function getDependencies ($level = 1)
	{
		return array();
	}

	public function isCompleted (Entities\Quest $quest, $term = null)
	{
		if ($this->getContext()->model->resourceRepository->checkResources($quest->owner, array('food' => pow($quest->level, 2) * 300))) {
			return TRUE;
		}
		return FALSE;
	}

	public function processCompletion (Entities\Quest $quest)
	{

	}

	public function getValue ($level = 1)
	{
		return $level * 1000;
	}

	public function getLevelCap ()
	{
		return 5;
	}
}
