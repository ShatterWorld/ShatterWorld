<?php
namespace Rules\Events;
use Entities;

class Pillaging extends Attack
{
	public function getDescription ()
	{
		return 'Loupežný útok';
	}
	
	public function process (Entities\Event $event)
	{
		$result = parent::process($event);
		if ($loot = $result['attacker']['loot']) {
			$this->getContext()->model->getResourceService()->pay($event->target->owner, $loot);
		}
		$this->getContext()->model->getMoveService()->startUnitMovement(
			$event->target, 
			$event->origin, 
			$event->origin->owner, 
			'unitReturn', 
			$event->getUnitList(), 
			$loot, 
			$event->term
		);
		return $result;
	}
	
	public function formatReport ($data)
	{
		return $data;
	}
}