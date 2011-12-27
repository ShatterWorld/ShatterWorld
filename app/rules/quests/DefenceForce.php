<?php
namespace Rules\Quests;
use Rules\AbstractRule;
use Entities;

class DefenceForce extends AbstractRule implements IQuest
{
	public function getDescription ()
	{
		return 'Obranná síla';
	}

	public function getExplanation (Entities\Quest $quest)
	{
		$attackForce = $this->getContext()->stats->units->getClanDefenceForce($quest->owner);
		return 'Zvyš celkovou obrannou sílu armády na ' . (30 * pow($quest->level, 3) + 250) . ' (' . $attackForce . ' splněno)';
	}

	public function getDependencies ($level = 1)
	{
		return array();
	}

	public function isCompleted (Entities\Quest $quest, $term = null)
	{
		if ($this->getContext()->stats->units->getClanDefenceForce($quest->owner) >= 30 * pow($quest->level, 3) + 250) {
			return TRUE;
		}
		return FALSE;
	}

	public function processCompletion (Entities\Quest $quest)
	{

	}

	public function getValue ($level = 1)
	{
		return 500 + $level * 100;
	}

	public function getLevelCap ()
	{
		return 10;
	}
}
