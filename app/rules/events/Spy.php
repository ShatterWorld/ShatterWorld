<?php
namespace Rules\Events;
use Rules\AbstractRule;
use ReportItem;
use DataRow;
use Entities;
use Nette\Diagnostics\Debugger;

abstract class Spy extends AbstractRule implements IEvent
{
	protected $attackingUnits;

	protected function evaluateSpy (Entities\Event $event)
	{
		$result = array(
			'successful' => FALSE,
			'field' => null,
			'researches' => array(),

			'attacker' => array(
				'units' => array(),
				'casualties' => array()
			),
			'defender' => array(
				'units' => array(),
				'casualties' => array()
			)
		);

		$attackerSpyCount = 0;
		foreach($event->getUnits() as $unit){
			if($unit->type === 'spy'){
				$attackerSpyCount = $unit->count;
				$result['attacker']['units'] = array('spy' => $attackerSpyCount);
				break;
			}
		}

		$defenderSpyCount = 0;
		foreach ($event->target->getUnits() as $unit) {
			if($unit->type === 'spy'){
				$defenderSpyCount = $unit->count;
				$result['defender']['units'] = array('spy' => $defenderSpyCount);
				break;
			}
		}

		$attackerPower = $attackerSpyCount * $this->getContext()->stats->units->getUnitLevel($event->owner, 'spy');
		$defenderPower = $defenderSpyCount * $this->getContext()->stats->units->getUnitLevel($event->target->owner, 'spy') + $this->getContext()->stats->units->getSpyForce($event->target->owner, 'spy');

		$attackerCasualtiesCoefficient = $defenderPower > 0 ? 1 - tanh($attackerPower / (5 * $defenderPower)) : 0;
		$defenderCasualtiesCoefficient = $defenderPower > 0 ? tanh(2 * $attackerPower / $defenderPower) : 1;

		$result['attacker']['casualties'] = array('spy' => intval(round($attackerSpyCount * $attackerCasualtiesCoefficient)));
		$result['defender']['casualties'] = array('spy' => intval(round($defenderSpyCount * $defenderCasualtiesCoefficient)));

		if ($result['attacker']['casualties']['spy'] <=  $attackerSpyCount/2){
			$result['successful'] = TRUE;
			$result['field'] = $event->target;
			$researches = $this->getContext()->model->getResearchRepository()->getResearched($event->target->owner);
			foreach ($researches as $research){
				$result['researches'][$research->type] = $research->level;
			}

		}

		return $result;
	}

	protected function returnAttackingUnits (Entities\Event $event, $processor)
	{
		$processor->queueEvent($this->getContext()->model->getMoveService()->startUnitReturn(
			$event->target,
			$event->origin,
			$event->origin->owner,
			$this->attackingUnits,
			null,
			$event->term
		));
	}

	public function process (Entities\Event $event, $processor)
	{
		$model = $this->getContext()->model;
		$result = $this->evaluateSpy($event);
		$this->attackingUnits = $event->getUnitList();
		$model->getUnitService()->moveUnits($event->target, $event->owner, $event->getUnits());
		if ($result['attacker']['casualties']) {
			$model->getUnitService()->removeUnits($event->owner, $event->target, $result['attacker']['casualties'], $event->term);
		}
		if ($result['defender']['casualties']) {
			$model->getUnitService()->removeUnits($event->target->owner, $event->target, $result['defender']['casualties'], $event->term);
		}
		$this->returnAttackingUnits($event, $processor);
		return $result;
	}

	public function isValid (Entities\Event $event)
	{
		return $event->target->owner !== NULL &&
			$event->target->owner !== $event->owner &&
			($event->owner->alliance === NULL || $event->target->owner->alliance !== $event->owner->alliance);
	}

	public function isRemote ()
	{
		return FALSE;
	}

}
