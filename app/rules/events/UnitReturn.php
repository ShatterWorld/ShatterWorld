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
	
	public function process (Entities\Event $event)
	{
		$this->getContext()->model->getUnitService()->moveUnits($event->target, $event->owner, $event->getUnits());
		if ($event->cargo) {
			$this->getContext()->model->getResourceService()->increase($event->owner, $event->cargo);
		}
		return array();
	}
	
	public function isValid (Entities\Event $event)
	{
		return TRUE;
	}
	
	public function formatReport (Entities\Report $report)
	{
		return array();
	}
}