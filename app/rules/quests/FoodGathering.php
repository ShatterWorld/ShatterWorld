<?php
namespace Rules\Quests;
use Rules\AbstractRule;

class FoodGathering extends AbstractRule
{
	public function getDependencies ($level = 1)
	{
		return array();
	}
	
	public function isCompleted (Entities\Quest $quest)
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