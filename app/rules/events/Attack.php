<?php
namespace Rules\Events;
use Rules\AbstractRule;
use ReportItem;
use DataRow;
use Entities;
use Nette\Diagnostics\Debugger;

abstract class Attack extends AbstractRule implements IEvent
{
	protected $attackingUnits;

	protected function evaluateBattle (Entities\Event $event)
	{
		$result = array(
			'successful' => FALSE,
			'totalVictory' => TRUE,
			'attacker' => array(
				'units' => array(),
				'casualties' => array(),
				'loot' => array()
			),
			'defender' => array(
				'units' => array(),
				'casualties' => array()
			)
		);
		$attackerPower = 0;
		$attackerDefense = 0;
		$defenderPower = 0;
		$defenderDefense = 0;
		foreach ($event->getUnits() as $unit) {
			$result['attacker']['units'][$unit->type] = $unit->count;
			$attackerPower = $attackerPower + $unit->count * $this->getContext()->stats->units->getUnitAttack($event->owner, $unit->type);
			$attackerDefense = $attackerDefense + $unit->count * $this->getContext()->stats->units->getUnitDefense($event->owner, $unit->type);
		}
		foreach ($event->target->getUnits() as $unit) {
			$result['defender']['units'][$unit->type] = $unit->count;
			$defenderPower = $defenderPower + $unit->count * $this->getContext()->stats->units->getUnitAttack($event->target->owner, $unit->type);
			$defenderDefense = $defenderDefense + $unit->count * $this->getContext()->stats->units->getUnitDefense($event->target->owner, $unit->type);
		}

		$fieldRule = $this->getContext()->rules->get('field', $event->target->type);
		if ($facility = $event->target->facility){
			$facilityRule = $this->getContext()->rules->get('facility', $facility->type);
			$defenderDefense *= (1 + $fieldRule->getDefenceBonus()) * (1 + $facilityRule->getDefenceBonus($facility->level));
		}
		$attackerCasualtiesCoefficient = $defenderDefense > 0 ? 1 - tanh($attackerPower / (5 * $defenderDefense)) : 0;
		$defenderCasualtiesCoefficient = $defenderDefense > 0 ? tanh(2 * $attackerPower / $defenderDefense) : 1;
		foreach ($event->getUnits() as $unit) {
			$result['attacker']['casualties'][$unit->type] = intval(round($unit->count * $attackerCasualtiesCoefficient));
		}
		foreach ($event->target->getUnits() as $unit) {
			$result['defender']['casualties'][$unit->type] = intval(round($unit->count * $defenderCasualtiesCoefficient));
			if ($result['defender']['casualties'][$unit->type] < $result['defender']['units'][$unit->type]) {
				$result['totalVictory'] = FALSE;
			}
		}
		$result['successful'] = $attackerPower > $defenderDefense;
		if ($result['successful']) {
			$resources = $this->getContext()->model->getResourceRepository()->getResourcesArray($event->target->owner);
			$territorySize = $this->getContext()->model->getFieldRepository()->getTerritorySize($event->target->owner);
			$unitCapacity = 0;
			foreach ($result['attacker']['units'] as $type => $count) {
				$rule = $this->getContext()->rules->get('unit', $type);
				$resultCount = $count - isset($result['attacker']['casualties'][$type]) ? $result['attacker']['casualties'][$type] : 0;
				$unitCapacity = $unitCapacity + $rule->getCapacity() * $resultCount;
			}
			foreach ($resources as $resource => $data) {
				$result['attacker']['loot'][$resource] = intval(floor($data['balance'] / $territorySize));
			}
		}
		return $result;
	}

	protected function returnAttackingUnits (Entities\Event $event, $processor, $loot = NULL)
	{
		if ($loot) {
			$this->getContext()->model->getResourceService()->pay($event->target->owner, $loot, $event->term);
		}
		$processor->queueEvent($this->getContext()->model->getMoveService()->startUnitReturn(
			$event->target,
			$event->origin,
			$event->origin->owner,
			$this->attackingUnits,
			$loot,
			$event->term
		));
	}

	protected function damageTargetFacility (Entities\Event $event, $level, $processor)
	{
		if ($facility = $event->target->facility) {
			$originalLevel = $facility->level;
			$newLevel = max(0, $facility->level - $level);
			if ($facility->damaged || $newLevel <= 0) {
				$this->getContext()->model->facilityService->delete($facility, FALSE);
			} else {
				$facility->setDamaged(TRUE);
				$facility->setLevel($facility->getLevel() - $level);
			}
			$rule = $this->getContext()->rules->get('facility', $facility->type);
			$value = 0;
			for ($i = $originalLevel; $i > $newLevel; $i--) {
				$value += $rule->getValue($i);
			}
			$this->getContext()->model->scoreService->decreaseClanScore($event->target->owner, 'facility', $value);
		}
	}
	
	public function process (Entities\Event $event, $processor)
	{
		$model = $this->getContext()->model;
		$result = $this->evaluateBattle($event);
		$this->attackingUnits = $event->getUnitList();
		$model->getUnitService()->moveUnits($event->target, $event->owner, $event->getUnits());
		if ($result['attacker']['casualties']) {
			$model->getUnitService()->removeUnits($event->owner, $event->target, $result['attacker']['casualties'], $event->term);
		}
		if ($result['defender']['casualties']) {
			$model->getUnitService()->removeUnits($event->target->owner, $event->target, $result['defender']['casualties'], $event->term);
		}
		return $result;
	}

	public function isValid (Entities\Event $event)
	{
		return $event->target->owner !== NULL &&
			$event->target->owner !== $event->owner &&
			($event->owner->alliance === NULL || $event->target->owner->alliance !== $event->owner->alliance) &&
			($this->getContext()->model->getFieldRepository()->isNeighbourOf($event->target, NULL) || $this->getContext()->model->getFieldRepository()->isNeighbourOf($event->target, $event->owner));
	}

	public function formatReport (Entities\Report $report)
	{
		$data = $report->data;
		$message = array(
			ReportItem::create('unitGrid', array(
				DataRow::from($data['attacker']['units'])->setLabel('Jednotky'),
				DataRow::from($data['attacker']['casualties'])->setLabel('Ztráty')
			))->setHeading('Útočník'),
			ReportItem::create('unitGrid', array(
				DataRow::from($data['defender']['units'])->setLabel('Jednotky'),
				DataRow::from($data['defender']['casualties'])->setLabel('Ztráty')
			))->setHeading('Obránce')
		);
		return $message;
	}
}
