<?php
namespace Rules\Quests;
use Rules\AbstractRule;
use Entities;

class DefenceForce extends AbstractQuest implements IQuest
{
	public function getDescription ()
	{
		return 'Obranná síla';
	}

	public function getExplanation (Entities\Quest $quest)
	{
		return 'Zvyš celkovou obrannou sílu armády na ' . $this->getTarget($quest) . ' (' . $this->getStatus($quest) . ' splněno)';
	}

	public function getValue ($level = 1)
	{
		return 500 + $level * 100;
	}

	public function getLevelCap ()
	{
		return 10;
	}
	public function getTarget (Entities\Quest $quest)
	{
		return 30 * pow($quest->level, 3) + 250;
	}

	public function getStatus (Entities\Quest $quest)
	{
		if (isset($this->status)){
			return $this->status;
		}
		$status = $this->getContext()->stats->units->getClanDefenceForce($quest->owner);
		$this->status = $status;
		return $status;

	}
}
