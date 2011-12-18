<?php
namespace Rules\Events;
use Rules\AbstractRule;
use ReportItem;
use DataRow;
use Entities;
use Nette\Diagnostics\Debugger;

class Spy extends AbstractRule implements IEvent
{
	protected $attackingUnits;

	public function getDescription ()
	{
		return 'Špionáž';
	}

	public function getExplanation (Entities\Event $event = null)
	{
		return 'Špionáž';
	}

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

		Debugger::barDump($event->getUnits());
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

		$attackerPower = $attackerSpyCount * $this->getContext()->stats->units->getSpyForce($event->owner, 'spy');
		$defenderPower = $defenderSpyCount * $this->getContext()->stats->units->getSpyForce($event->target->owner, 'spy');

		$attackerCasualtiesCoefficient = $defenderPower > 0 ? 1 - tanh($attackerPower / (5 * $defenderPower)) : 0;
		$defenderCasualtiesCoefficient = $defenderPower > 0 ? tanh(2 * $attackerPower / $defenderPower) : 1;

		$result['attacker']['casualties'] = array('spy' => intval(round($attackerSpyCount * $attackerCasualtiesCoefficient)));
		$result['defender']['casualties'] = array('spy' => intval(round($defenderSpyCount * $defenderCasualtiesCoefficient)));

		if ($result['attacker']['casualties']['spy'] >  $attackerSpyCount/2){
			$result['successful'] = TRUE;
			$result['field'] = $event->target;
			$result['researches'] = $this->getContext()->model->getResearchRepository()->getResearched($event->owner);

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
		return $result;
	}

	public function isValid (Entities\Event $event)
	{
		return $event->target->owner !== NULL &&
			$event->target->owner !== $event->owner &&
			($event->owner->alliance === NULL || $event->target->owner->alliance !== $event->owner->alliance);
	}

	public function formatReport (Entities\Report $report)
	{
		$data = $report->data;
//		Debugger::barDump($data['attacker']['casualties']);
		foreach($data['attacker']['casualties'] as $key => $value){
			Debugger::barDump($key);
			Debugger::barDump($value);

		}

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
