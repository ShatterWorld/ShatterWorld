<?php
namespace Rules\Events;
use Rules\AbstractRule;
use ReportItem;
use DataRow;
use Entities;
use Nette\Diagnostics\Debugger;

class Investigation extends Spy implements IEvent
{
	public function getDescription ()
	{
		return 'Špionáž';
	}

	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Špionáž  klanu %s na poli %s', $event->target->owner->name, $event->target->getCoords());
	}

	protected function evaluateSpy (Entities\Event $event)
	{
		$result = array(
			'successful' => FALSE,
			'targetCoords' => '',
			'researches' => array(),
			'resources' => array(),
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
				$result['attacker']['units']['spy'] = $attackerSpyCount;
				break;
			}
		}

		$defenderSpyCount = 0;
		$militiaDefence = $this->getContext()->model->getResearchRepository()->getResearchLevel($event->target->owner, 'militia') >= 3 ;
		foreach ($event->target->getUnits() as $unit) {
			if($unit->type === 'spy' || ($unit->type === 'militia' && $militiaDefence)){
				$defenderSpyCount += $unit->count;
				$result['defender']['units'][$unit->type] = $defenderSpyCount;
			}
		}

		$attackerPower = $attackerSpyCount * $this->getContext()->stats->units->getUnitLevel($event->owner, 'spy');
		$defenderPower = $defenderSpyCount * $this->getContext()->stats->units->getUnitLevel($event->target->owner, 'spy') + $this->getContext()->stats->units->getSpyForce($event->target->owner, 'spy');

		$attackerCasualtiesCoefficient = $defenderPower > 0 ? 1 - tanh($attackerPower / (5 * $defenderPower)) : 0;
		$defenderCasualtiesCoefficient = $defenderPower > 0 ? tanh(2 * $attackerPower / $defenderPower) : 1;

		$result['attacker']['casualties'] = array('spy' => intval(round($attackerSpyCount * $attackerCasualtiesCoefficient)));
		$result['defender']['casualties'] = array('spy' => intval(round($defenderSpyCount * $defenderCasualtiesCoefficient)));

		$result['targetCoords'] = $event->target->getCoords();
		if ($attackerPower > $defenderPower){
			$result['successful'] = TRUE;
			if ($event->target->facility){
				$result['facility'] = array();
				$result['facility']['type'] = $event->target->facility->type;
				$result['facility']['level'] = $event->target->facility->level;

			}
			$researches = $this->getContext()->model->getResearchRepository()->getResearched($event->target->owner);
			foreach ($researches as $research){
				$result['researches'][$research->type] = $research->level;
			}
			$result['territorySize'] = $this->getContext()->model->getFieldRepository()->getTerritorySize($event->target->owner);
			$result['resources'] = $this->getContext()->model->getResourceRepository()->getResourcesArray($event->target->owner);
		}

		return $result;
	}

	public function formatReport (Entities\Report $report)
	{
		$data = $report->data;

		$message = '';
		if ($report->type === 'owner') {
			if ($data['successful']) {
				$message = array(ReportItem::create('text', 'Špionáž se naší rozvědce zdařila!'));

				if (isset($data['facility'])){
					$message[] = ReportItem::create('text', 'Na poli ' . $data['targetCoords'] . ' stojí ' . $this->getContext()->rules->get('facility', $data['facility']['type'])->getDescription() . ' (úroveň ' . $data['facility']['level'] . ')');
				}
				else{
					$message[] = ReportItem::create('text', 'Na poli ' . $data['targetCoords'] . ' nestojí žádná budova.');
				}

				$message[] = ReportItem::create('text', 'Rozloha klanu: ' . $data['territorySize']);

				$message[] = ReportItem::create('unitGrid', array(
						DataRow::from($data['attacker']['units'])->setLabel('Jednotky'),
						DataRow::from($data['attacker']['casualties'])->setLabel('Ztráty')
					))->setHeading('Útočník');

				$message[] = ReportItem::create('unitGrid', array(
						DataRow::from($data['defender']['units'])->setLabel('Jednotky'),
						DataRow::from($data['defender']['casualties'])->setLabel('Ztráty'),
					))->setHeading('Obránce');

				$message[] = ReportItem::create('researchGrid', array(
						DataRow::from($data['researches'])->setLabel('Výzkumy')
					))->setHeading('Výzkumy');

				$balance = array();
				$production = array();
				$storage = array();
				foreach($data['resources'] as $name => $resource){
					$balance[$name] = floor($resource['balance']);
					$production[$name] = floor($resource['production'] * 3600);
					$storage[$name] = floor($resource['storage']);
				}
				$message[] = ReportItem::create('resourceGrid', array(
						DataRow::from($balance)->setLabel('Ve skladech'),
						DataRow::from($production)->setLabel('Produkce'),
						DataRow::from($storage)->setLabel('Kapacita skladů')
					))->setHeading('Suroviny');
			} else {
				$message = array(ReportItem::create('text', 'Akce naší rozvědky se nezdařila! Měl bys posílit řady špionů!'));
				$message[] = ReportItem::create('unitGrid', array(
						DataRow::from($data['attacker']['units'])->setLabel('Jednotky'),
						DataRow::from($data['attacker']['casualties'])->setLabel('Ztráty')
					))->setHeading('Útočník');
			}
		} else {
			if ($data['successful']) {
				$message = array(ReportItem::create('text', 'Nepřítel infiltroval tvůj klan! Měl bys posílit řady špionů!'));
				$message[] = ReportItem::create('unitGrid', array(
						DataRow::from($data['defender']['units'])->setLabel('Jednotky'),
						DataRow::from($data['defender']['casualties'])->setLabel('Ztráty'),
					))->setHeading('Obránce');
			} else {
				$message = array(ReportItem::create('text', 'Nepříteli se nepodařilo infiltrovat tvůj klan!'));
				$message[] = ReportItem::create('unitGrid', array(
						DataRow::from($data['attacker']['units'])->setLabel('Jednotky'),
						DataRow::from($data['attacker']['casualties'])->setLabel('Ztráty')
					))->setHeading('Útočník');

				$message[] = ReportItem::create('unitGrid', array(
						DataRow::from($data['defender']['units'])->setLabel('Jednotky'),
						DataRow::from($data['defender']['casualties'])->setLabel('Ztráty'),
					))->setHeading('Obránce');
			}
		}
		return $message;
	}
}
