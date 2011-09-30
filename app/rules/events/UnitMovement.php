<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Entities;

class UnitMovement extends AbstractRule implements IEvent
{
	public function process (Entities\Event $event)
	{
		$this->context->model->getUnitService()->moveUnits($event->units, $event->target);
	}
	
	public function isValid (Entities\Event $event)
	{
		return $event->target->owner == $event->origin->owner;
	}
}