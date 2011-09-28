<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Nette\Utils\Neon;
use Entities;

class UnitTraining extends AbstractRule implements IEvent
{
	public function process (Entities\Event $event)
	{
		$this->getContext()->model->getUnitService()->addUnits($event->target, Neon::decode($event->construction));
	}
	
	public function isValid (Entities\Event $event)
	{
		return TRUE;
	}
}