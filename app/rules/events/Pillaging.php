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
		$this->getContext()->model->getMoveService()->startUnitMovement($event->target, $event->origin, 'pillagingReturn', $event->getUnitList());
		return $result;
	}
	
	public function formatReport ($data)
	{
		return $data;
	}
}