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
			$capacity = $capacity + $unit->count * $rule->getCapacity();
			$units[$unit->type] = $unit->count;
		}
		Debugger::barDump($capacity);
		$unitList = $event->getUnitList();
		$this->getContext()->model->getUnitService()->moveUnits($event->target, $event->owner, $event->getUnits());

		$bonus = $this->getContext()->stats->exploration->getBonus($event->owner);
		Debugger::barDump($bonus);
		$potential = $this->getContext()->rules->get('field', $event->target->type)->getProductionBonuses();
		Debugger::barDump($potential);
		$totalPotential = 0;
		foreach ($potential as $name => $resource){
			if ($resource > 0){
				$totalPotential += $resource;
			}
		}
		Debugger::barDump($totalPotential);
		$totalPotential *= $bonus;
		Debugger::barDump($totalPotential);
		$ratio = $capacity / $totalPotential;
		Debugger::barDump($ratio);

		foreach ($potential as $name => $resource){
			if ($resource > 0){
				$loot = floor($resource * $bonus * $bonus / $ratio);
				Debugger::barDump($loot);
				$cargo[$name] = $loot;
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
