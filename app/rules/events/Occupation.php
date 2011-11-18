<?php
namespace Rules\Events;
use ReportItem;
use Entities;

class Occupation extends Attack
{
	public function getDescription ()
	{
		return 'Dobyvačný útok';
	}
	
	public function process (Entities\Event $event, $processor)
	{
		$result = parent::process($event, $processor);
		$model = $this->getContext()->model;
		if ($result['totalVictory']) {
			foreach ($model->getConstructionRepository()->findBy(array('target' => $event->target->id)) as $construction) {
				$construction->failed = TRUE;
			}
			if ($loot = $result['attacker']['loot']) {
				$this->getContext()->model->getResourceService()->pay($event->target->owner, $loot, FALSE);
				$this->getContext()->model->getResourceService()->increase($event->owner, $loot, FALSE);
			}
			$event->target->owner = $event->owner;
		} else {
			$this->returnAttackingUnits($event, $processor);
		}
		return $result;
	}
	
	public function formatReport (Entities\Report $report)
	{
		$data = $report->data;
		$message = array(ReportItem::create('text', $data['totalVictory'] ? 'Vítězství' : 'Útok odražen'));
		$message = array_merge($message, parent::formatReport($report));
		if ($data['totalVictory'] && $data['attacker']['loot']) {
			$message[] = ReportItem::create('resourceGrid', array($data['attacker']['loot']))->setHeading('Kořist');
		}
		return $message;
	}
	
	public function isValid (Entities\Event $event)
	{
		if (parent::isValid($event)) {
			$valid = FALSE;
			foreach ($this->getContext()->model->getFieldRepository()->getFieldNeighbours($event->target) as $neighbour) {
				if ($neighbour->owner === $event->owner) {
					$valid = TRUE;
					break;
				}
			}
			return $valid;
		} else {
			return FALSE;
		}
	}
}