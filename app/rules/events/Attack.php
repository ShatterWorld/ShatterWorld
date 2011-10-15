<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Entities;

abstract class Attack extends AbstractRule implements IEvent
{
	protected function evaluateBattle (Entities\Event $event)
	{
		return array(
			'winner' => $event->owner,
			'attackerCasualties' => array(),
			'defenderCasualties' => array(),
			'loot' => array()
		);
	}
	
	public function isValid (Entities\Event $event)
	{
		return $event->target->owner !== NULL && 
			$event->target->owner !== $event->owner && 
			($event->owner->alliance === NULL || $event->target->owner->alliance !== $event->owner->alliance);
	}
}