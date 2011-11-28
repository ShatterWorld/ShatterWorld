<?php
namespace Rules\Events;
use ReportItem;
use DataRow;
use Entities;

class Pillaging extends Attack
{
	public function getDescription ()
	{
		return 'Loupežný útok';
	}
	
	public function process (Entities\Event $event, $processor)
	{
		$result = parent::process($event, $processor);
		$this->returnAttackingUnits($event, $processor, $result['attacker']['loot']);
		return $result;
	}
	
	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Loupeživý útok na %s', $event->target->getCoords());
	}
	
	public function formatReport (Entities\Report $report)
	{
		$data = $report->data;
		$message = array(ReportItem::create('text', $data['successful'] ? 'Vítězství' : 'Porážka'));
		$message = array_merge($message, parent::formatReport($report));
		if ($data['successful'] && $data['attacker']['loot']) {
			$message[] = ReportItem::create('resourceGrid', array($data['attacker']['loot']))->setHeading('Kořist');
		}
		return $message;
	}
}