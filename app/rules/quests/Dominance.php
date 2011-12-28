<?php
namespace Rules\Quests;
use Rules\AbstractRule;
use Entities;

class Dominance extends AbstractQuest implements IQuest
{
	public function getDescription ()
	{
		return 'Dominance';
	}

	public function getExplanation (Entities\Quest $quest)
	{
		return 'Proveď ' . $this->getTarget($quest) . ' totálních vítězství během 24hod (' . $this->getStatus($quest) . ' splněno)';
	}

	public function getValue ($level = 1)
	{
		return 500 + pow($level, 2) * 250;
	}

	public function getLevelCap ()
	{
		return 10;
	}

	public function getTarget (Entities\Quest $quest)
	{
		return 3 * pow($quest->level, 2) + 3;
	}

	public function getStatus (Entities\Quest $quest)
	{
		if (isset($this->status)){
			return $this->status;
		}
		$status = $this->getContext()->model->getReportRepository()->getTotalVictoryCount($quest->owner);
		$this->status = $status;
		return $status;

	}

}
