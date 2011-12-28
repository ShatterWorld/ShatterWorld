<?php
namespace Rules\Quests;
use Rules\AbstractRule;
use Entities;

class SpyForce extends AbstractQuest implements IQuest
{
	public function getDescription ()
	{
		return 'Síla rozvědky';
	}

	public function getExplanation (Entities\Quest $quest)
	{
		return 'Zvyš sílu rozvětky na ' . $this->getTarget($quest) . ' (' . $this->getStatus($quest) . ' splněno)';
	}


	public function getValue ($level = 1)
	{
		return $level * 1500;
	}

	public function getLevelCap ()
	{
		return 10;
	}
	public function getTarget (Entities\Quest $quest)
	{
		return pow($quest->level, 2) + 14;
	}

	public function getStatus (Entities\Quest $quest)
	{
		if (isset($this->status)){
			return $this->status;
		}
		$status = $this->getContext()->stats->units->getSpyForce($quest->owner);
		$this->status = $status;
		return $status;

	}
}
