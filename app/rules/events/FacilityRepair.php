<?php
namespace Rules\Events;
use Entities;
use Rules\AbstractRule;
use ReportItem;

class FacilityRepair extends AbstractRule implements IEvent
{
	public function getDescription ()
	{
		return 'Oprava budovy';
	}
	
	public function process (Entities\Event $event, $processor)
	{
		$event->target->facility->damaged = FALSE;
		return array(
			'facility' => $event->target->facility->type,
			'location' => $event->target->getCoords()
		);
	}
	
	public function isValid (Entities\Event $event)
	{
		return $event->target->owner === $event->owner && $event->target->facility && $event->target->facility->damaged;
	}
	
	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Oprava budovy %s na %s', $this->getContext()->rules->get('facility', $event->target->facility->type)->getDescription(), $event->target->getCoords());
	}
	
	public function formatReport (Entities\Report $report)
	{
		$data = $report->data;
		return array(
			ReportItem::create('text', sprintf('Budova %s na poli %s byla opravena.', $data['facility'], $data['location']))
		);
	}
	
	public function isExclusive ()
	{
		return TRUE;
	}
}