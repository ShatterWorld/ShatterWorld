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
		$attackPower = 0;
		$defensePower = 0;
		foreach ($event->getUnits() as $unit) {
			$rule = $this->getContext()->rules->get('unit', $unit->type);
			$result['attacker']['units'][$unit->type] = $unit->count;
			$attackPower = $attackPower + $unit->count * $rule->getAttack();
		}
		foreach ($event->target->getUnits() as $unit) {
			$rule = $this->getContext()->rules->get('unit', $unit->type);
			$result['defender']['units'][$unit->type] = $unit->count;
			$defensePower = $defensePower + $unit->count * $rule->getDefense();
		}
		$result['successful'] = $attackPower > $defensePower;
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
		return $result;
	}
	
	public function isValid (Entities\Event $event)
	{
		return $event->target->owner !== NULL && 
			$event->target->owner !== $event->owner && 
			($event->owner->alliance === NULL || $event->target->owner->alliance !== $event->owner->alliance);
	}
}