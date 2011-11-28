<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Nette\Utils\Json;
use Entities;
use ReportItem;

class UnitTraining extends AbstractRule implements IConstruction
{
	public function getDescription ()
	{
		return 'Trénink jednotek';
	}

	public function process (Entities\Event $event, $processor)
	{
		$this->getContext()->model->getUnitService()->addUnits($event->target, $event->owner, Json::decode($event->construction, Json::FORCE_ARRAY));
		return array();
	}
	
	public function isValid (Entities\Event $event)
	{
		return TRUE;
	}
	
	public function formatReport (Entities\Report $report)
	{
		return array(
			ReportItem::create('unitGrid', array((array) Json::decode($report->event->construction)))
		);
	}
	
	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Výcvik jednotek');
	}
	
	public function isExclusive ()
	{
		return FALSE;
	}
}