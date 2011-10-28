<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Entities;

class UnitMovement extends AbstractRule implements IEvent
{
	public function getDescription ()
	{
		return 'Přesun jednotek';
	}

	public function process (Entities\Event $event)
	{
		$this->context->model->getUnitService()->moveUnits($event->units, $event->owner, $event->target);
		return array();
	}
	
	public function isValid (Entities\Event $event)
	{
		return $event->target->owner == $event->origin->owner;
	}
	
	public function formatReport (Entities\Report $report)
	{
		return array();
	}
}