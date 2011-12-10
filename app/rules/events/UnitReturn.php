<?php
namespace Rules\Events;
use Entities;
use Rules\AbstractRule;

class UnitReturn extends AbstractRule implements IEvent
{
	public function getDescription ()
	{
		return 'Návrat jednotek';
	}
	
	public function process (Entities\Event $event, $processor)
	{
		$this->getContext()->model->getUnitService()->moveUnits($event->target, $event->owner, $event->getUnits());
		if ($event->cargo) {
			$this->getContext()->model->getResourceService()->increase($event->owner, $event->cargo, $event->term);
		}
		return array();
	}
	
	public function isValid (Entities\Event $event)
	{
		return TRUE;
	}
	
	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Návrat jednotek z %s do %s', $event->origin->getCoords(), $event->target->getCoords());
	}
	
	public function formatReport (Entities\Report $report)
	{
		return array();
	}
}