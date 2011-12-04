<?php
namespace Rules\Events;
use Entities;
use Rules\AbstractRule;
use Nette\Diagnostics\Debugger;

class ResourceExhaustion extends AbstractRule implements IEvent
{
	public function getDescription ()
	{
		return 'Vymření jednotek';
	}

	public function process (Entities\Event $event, $processor)
	{
		//count and kill

		//$this->getContext()->model->getResourceService()->increase($event->target->owner, $event->cargo);
		return array();
	}

	public function isValid (Entities\Event $event)
	{
		return true;
	}

	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Vyření jednotek na nedostatek surovin');
	}

	public function formatReport (Entities\Report $report)
	{
		return array();
	}
}
