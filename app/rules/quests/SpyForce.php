<?php
namespace Rules\Quests;
use Rules\AbstractRule;
use Entities;

class SpyForce extends AbstractRule implements IQuest
{
	public function getDescription ()
	{
		return 'Síla rozvědky';
	}

	public function getExplanation (Entities\Quest $quest)
	{
		$spyForce = $this->getContext()->stats->units->getSpyForce($quest->owner);
		return 'Zvyš sílu rozvětky na ' . (pow($quest->level, 2) + 14) . ' (' . $spyForce . ' splněno)';
	}

	public function getDependencies ($level = 1)
	{
		return array();
	}

	public function isCompleted (Entities\Quest $quest, $term = null)
	{
		if ($this->getContext()->stats->units->getSpyForce($quest->owner) >= pow($quest->level, 2) + 14) {
			return TRUE;
		}
		return FALSE;
	}

	public function processCompletion (Entities\Quest $quest)
	{

	}

	public function getValue ($level = 1)
	{
		return $level * 1500;
	}

	public function getLevelCap ()
	{
		return 10;
	}
}
