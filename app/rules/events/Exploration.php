<?php
namespace Rules\Events;
use Rules\AbstractRule;
use ReportItem;
use Entities;

class Exploration extends AbstractRule implements IEvent
{
	public function getDescription ()
	{
		return 'Průzkum';
	}

	public function process (Entities\Event $event, $processor)
	{
		$cargo = array();
		$this->getContext()->model->getUnitService()->moveUnits($event->target, $event->owner, $event->getUnits());
		foreach($this->getContext()->rules->get('field', $event->target->type)->getProductionBonuses() as $name => $resource){
			if ($resource > 0){
				$cargo[$name] = floor($resource * rand($this->getContext()->params['game']['stats']['minExplorationCoefficient'], $this->getContext()->params['game']['stats']['minExplorationCoefficient']));
			}
		}
		$processor->queueEvent($this->getContext()->model->getMoveService()->startUnitMovement(
			$event->target, 
			$event->origin, 
			$event->origin->owner, 
			'unitReturn', 
			$event->getUnitList(), 
			$cargo, 
			$event->term
		));
		return array('resources' => $cargo, 'units' => $event->getUnitList());
	}

	public function isValid (Entities\Event $event)
	{
		if ($this->getContext()->model->getConstructionRepository()->findOneBy(array('target' => $event->target->id, 'owner' => $event->owner->id))) {
			return FALSE;
		}
		return $event->target->owner === NULL;
	}

	public function formatReport (Entities\Report $report)
	{
		$data = $report->data;
		return array(
			ReportItem::create('text', 'Prozkoumáno!'),
			ReportItem::create('unitGrid', array($data['units']))->setHeading('Jednotky'),
			ReportItem::create('resourceGrid', array($data['resources']))->setHeading('Zisk')
		);
	}
}
