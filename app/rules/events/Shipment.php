<?php
namespace Rules\Events;
use Entities;
use Rules\AbstractRule;
use Nette\Diagnostics\Debugger;

class Shipment extends AbstractRule implements IEvent
{
	public function getDescription ()
	{
		return 'Zásilka surovin';
	}

	public function process (Entities\Event $event, $processor)
	{
		$this->getContext()->model->getResourceService()->increase($event->target->owner, $event->cargo);
		return array();
	}

	public function isValid (Entities\Event $event)
	{
		return true;
	}

	public function formatReport (Entities\Report $report)
	{
		return array();
	}
}
