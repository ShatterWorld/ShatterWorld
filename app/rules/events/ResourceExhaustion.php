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
		$resourceRule = $this->getContext()->rules->get('resource', $event->construction);
		$report = $resourceRule->processExhaustion($event);
		$report['resource'] = $event->construction;
		return $report;
	}

	public function isValid (Entities\Event $event)
	{
		return true;
	}

	public function getExplanation (Entities\Event $event)
	{
		return $this->getContext()->rules->get('resource', $event->construction)->getExhaustionExplanation();
	}

	public function formatReport (Entities\Report $report)
	{
		return $this->getContext()->rules->get('resource', $report->data['resource'])->formatExhaustionReport($report);
	}

	public function isRemote ()
	{
		return FALSE;
	}

	public function isExclusive ()
	{
		return false;
	}
}
