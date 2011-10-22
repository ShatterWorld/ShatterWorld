<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Entities;

abstract class Attack extends AbstractRule implements IEvent
{
	protected function evaluateBattle (Entities\Event $event)
	{
		$result = array(
			'successful' => TRUE,
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
			$rule = $this->getContext()->rules->get('unit', $unit->type);
			$result['attacker']['units'][$unit->type] = $unit->count;
			$attackerPower = $attackerPower + $unit->count * $rule->getAttack();
			$attackerDefense = $attackerDefense + $unit->count * $rule->getDefense();
		}
		foreach ($event->target->getUnits() as $unit) {
			$rule = $this->getContext()->rules->get('unit', $unit->type);
			$result['defender']['units'][$unit->type] = $unit->count;
			$defenderPower = $defenderPower + $unit->count * $rule->getAttack();
			$defenderDefense = $defenderDefense + $unit->count * $rule->getDefense();
		}
		$result['successful'] = $attackerPower > $defenderDefense;
		$attackerCasualtiesCoefficient = 1 - tanh($attackerPower / 5 * $defenderDefense);
		$defenderCasualtiesCoeffecient = tanh(3 * $attackerPower / $defenderDefense);
		foreach ($event->getUnits() as $unit) {
			$result['attacker']['casualties'][$unit->type] = intval(floor($unit->count * $attackerCasualtiesCoefficient));
		}
		foreach ($event->target->getUnits() as $unit) {
			$result['defender']['casualties'][$unit->type] = intval(floor($unit->count * $defenderCasualtiesCoefficient));
		}
		if ($result['successful']) {
			$resources = $this->getContext()->model->getResourceRepository()->findResourcesByClan($event->target->owner);
			$territorySize = $this->getContext()->model->getFieldRepository()->getTerritorySize($event->target->owner);
			foreach ($resources as $resource => $amount) {
				$result['attacker']['loot'][$resource] = intval(floor($amount / $territorySize));
			}
		}
		return $result;
	}
	
	public function process (Entities\Event $event)
	{
		$model = $this->getContext()->model;
		$model->getUnitService()->moveUnits($event->target, $event->owner, $event->getUnits());
		$result = $this->evaluateBattle($event);
		if ($casualties = array_merge($result['attacker']['casualties'], $result['defender']['casualties'])) {
			$model->getUnitService()->removeUnits($casualties);
		}
		if ($this->isReturning()) {
			if ($loot = $result['attacker']['loot']) {
				$this->getContext()->model->getResourceService()->pay($event->target->owner, $loot);
			}
			$this->getContext()->model->getMoveService()->startUnitMovement(
				$event->target, 
				$event->origin, 
				$event->origin->owner, 
				'unitReturn', 
				$event->getUnitList(), 
				$loot, 
				$event->term
			);
		}
		return $result;
	}
	
	public function isValid (Entities\Event $event)
	{
		return $event->target->owner !== NULL && 
			$event->target->owner !== $event->owner && 
			($event->owner->alliance === NULL || $event->target->owner->alliance !== $event->owner->alliance);
	}
	
	public function isReturning ()
	{
		return TRUE;
	}
}