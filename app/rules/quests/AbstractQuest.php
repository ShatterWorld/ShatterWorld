<?php
namespace Rules\Quests;
use Nette;
use Rules;
use Entities;

abstract class AbstractQuest extends Rules\AbstractRule implements IQuest
{
	/**
	 * Status
	 * @var int
	 */
	protected $status;

	public function getDependencies ($level = 1)
	{
		return array();
	}

	public function processCompletion (Entities\Quest $quest)
	{}

	public function isCompleted (Entities\Quest $quest, $term = null)
	{
		if ($this->getStatus($quest) >= $this->getTarget($quest)) {
			return TRUE;
		}
		return FALSE;
	}

}
