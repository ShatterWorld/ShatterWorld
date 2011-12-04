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
		$resourceRule = $this->getContext()->rules->get('resources', $event->construction);
		$resourceRule->processExhaustion($event->owner);
		return array();
	}

	public function isValid (Entities\Event $event)
	{
		return true;
	}

	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Vymření jednotek na nedostatek surovin');
	}

	public function formatReport (Entities\Report $report)
	{
		return array();
	}

	public function isExclusive ()
	{
		return false;
	}
}
