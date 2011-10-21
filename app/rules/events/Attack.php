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
		if ($result['attacker']['loot']) {
			$model->getResourceService()->increase($event->owner, $result['attacker']['loot']);
		}
	}
	
	public function isValid (Entities\Event $event)
	{
		return $event->target->owner !== NULL && 
			$event->target->owner !== $event->owner && 
			($event->owner->alliance === NULL || $event->target->owner->alliance !== $event->owner->alliance);
	}
}