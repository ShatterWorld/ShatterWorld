<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Entities;

class AttackNotification extends AbstractRule implements IEvent
{
	public function getDescription ()
	{
		return 'Upozornění na útok';
	}
	
	public function process (Entities\Event $event, $processor)
	{
		return NULL;
	}
	
	public function isValid (Entities\Event $event)
	{
		return TRUE;
	}
	
	public function formatReport (Entities\Report $report)
	{
		return array();
	}
	
	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Varování: %s', $this->getContext()->rules->get('event', $event->subject->type)->getExplanation($event->subject));
	}
}