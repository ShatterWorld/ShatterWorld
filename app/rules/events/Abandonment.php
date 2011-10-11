<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Entities;

class Abandonment extends AbstractRule implements IConstruction
{
	public function getDescription ()
	{
		return 'Opuštění území';
	}

	public function process (Entities\Event $event)
	{
		$event->target->setOwner(NULL);
		$event->target->setFacility(NULL);
		$this->getContext()->model->getFieldService()->invalidateVisibleFields($event->owner->id);
	}
	
	public function isValid (Entities\Event $event)
	{
		return $event->target->owner === $event->owner;
	}
	
	public function isExclusive ()
	{
		return TRUE;
	}
}