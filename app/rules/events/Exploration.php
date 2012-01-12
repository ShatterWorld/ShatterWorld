<?php
namespace Rules\Events;
use Rules\AbstractRule;
use ReportItem;
use Entities;
use Nette\Diagnostics\Debugger;

class Exploration extends AbstractRule implements IEvent
{
	public function getDescription ()
	{
		return 'Průzkum';
	}

	public function process (Entities\Event $event, $processor)
	{
		$cargo = array();
		$capacity = 0;
		$units = array();
		foreach ($event->getUnits() as $unit) {
			$rule = $this->getContext()->rules->get('unit', $unit->type);
			$capacity += $unit->count * $rule->getCapacity();
			Debugger::fireLog($rule->getCapacity());
			$units[$unit->type] = $unit->count;
		}
		$unitList = $event->getUnitList();
		$this->getContext()->model->getUnitService()->moveUnits($event->target, $event->owner, $event->getUnits());

		$potential = $this->getContext()->rules->get('field', $event->target->type)->getProductionBonuses();

		$sum = 0;
		$bonus = $this->getContext()->stats->exploration->getBonus($event->owner);
		Debugger::fireLog($bonus);
		foreach ($potential as $name => $resource){
			if ($resource > 0){
				$potential[$name] = $resource * 100 * $bonus;
				$sum += $resource * 100 * $bonus;
			}
		}

		if ($sum > 0){
			$k = $capacity / $sum;
			foreach ($potential as $name => $resource){
				if ($resource > 0){
					$cargo[$name] = floor(min($resource * $k, $resource));
				}
			}
		}

		$processor->queueEvent($this->getContext()->model->getMoveService()->startUnitReturn(
			$event->target,
			$event->origin,
			$event->origin->owner,
			$unitList,
			$cargo,
			$event->term
		));
		return array('resources' => $cargo, 'units' => $units);
	}

	public function isValid (Entities\Event $event)
	{
		if ($this->getContext()->model->getConstructionRepository()->findBy(array('target' => $event->target->id, 'owner' => $event->owner->id, 'processed' => FALSE))) {
			return FALSE;
		}
		return $event->target->owner === NULL;
	}

	public function isRemote ()
	{
		return FALSE;
	}

	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Průzkum pole %s', $event->target->getCoords());
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
