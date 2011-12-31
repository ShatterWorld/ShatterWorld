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

	public function process (Entities\Event $event, $processor)
	{
		$this->getContext()->model->getUnitService()->moveUnits($event->target, $event->owner, $event->units);
		return array();
	}

	public function isValid (Entities\Event $event)
	{
		return ($event->target->owner === $event->origin->owner) || ($event->target->owner->alliance === $event->origin->owner->alliance);
	}

	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Přesun jednotek');
	}

	public function formatReport (Entities\Report $report)
	{
		return array();
	}
}
