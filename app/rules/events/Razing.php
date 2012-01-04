<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Entities;
use ReportItem;
use DataRow;

class Razing extends Attack implements IEvent
{
	public function getDescription ()
	{
		return 'Ničivý útok';
	}

	public function process (Entities\Event $event, $processor)
	{
		$result = parent::process($event, $processor);
		if (isset($result['victory']) && $result['victory']) {
			$this->damageTargetBuilding($event, 3, $processor);
		}
		$this->returnAttackingUnits($event, $processor, $result['attacker']['loot']);
		return $result;
	}

	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Ničivý útok na %s', $event->target->getCoords());
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

	public function isValid (Entities\Event $event)
	{
		return TRUE;
	}
}
