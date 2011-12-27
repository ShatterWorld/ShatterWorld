<?php
namespace Rules\Quests;
use Rules\AbstractRule;
use Entities;

class RemoteExploration extends AbstractQuest implements IQuest
{
	public function getDescription ()
	{
		return 'Vzdálený průzkum';
	}

	public function getExplanation (Entities\Quest $quest)
	{
		return 'Proveď ' . $this->getTarget($quest) . ' průzkumů vzdálených alespoň 11 polí od velitelství (' . $this->getStatus($quest) . ' splněno)';
	}

	public function getValue ($level = 1)
	{
		return 300 + pow($level, 2) * 200;
	}

	public function getLevelCap ()
	{
		return 5;
	}

	public function getTarget (Entities\Quest $quest)
	{
		return 10 * $quest->level + 5;
	}

	public function getStatus (Entities\Quest $quest)
	{
		if (isset($this->status)){
			return $this->status;
		}
		$status = $this->getContext()->model->getEventRepository()->getRemoteExploration($quest->owner, 11);
		$this->status = $status;
		return $status;

	}

}
