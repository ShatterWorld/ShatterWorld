<?php
namespace Rules\Resources;
use Rules\AbstractRule;
use Entities;

class Fuel extends AbstractRule implements IExhaustableResource
{
	public function getDescription ()
	{
		return 'Palivo';
	}

	public function processExhaustion (Entities\Event $event)
	{
		$report = array(
			'units' => array()
		);
		$clan = $event->owner;
		$resources = $this->getContext()->model->getResourceRepository()->getResourcesArray($clan);
		$units = $this->getContext()->model->getUnitRepository()->getStaticClanUnits($clan);

		$rules = $this->getContext()->rules;
		//sort units in reverse order
		usort($units, function ($a, $b) use ($rules) {
			$aUpkeep = $rules->get('unit', $a->type)->getUpkeep();
			$bUpkeep = $rules->get('unit', $b->type)->getUpkeep();
			return (isset($bUpkeep['fuel']) ? $bUpkeep['fuel'] : 0)
				- (isset($aUpkeep['fuel']) ? $aUpkeep['fuel'] : 0);
		});

		$production = -$resources['fuel']['production'];

		foreach ($units as $unit) {
			if ($production <= 0) {
				break;
			}
			$upkeep = $rules->get('unit', $unit->type)->getUpkeep();
			if ($upkeep['fuel'] <= 0) {
				continue;
			}
			$unkept = min($unit->count, ceil($production / $upkeep['fuel']));
			$production -= $upkeep['fuel'] * $unkept;
			$report['units'][$unit->type] = $unkept;
			$this->getContext()->model->unitService->removeUnits($clan, $unit->location, array($unit->type => $unkept), $event->term, FALSE);
		}
		$this->getContext()->model->resourceService->recalculateProduction($event->owner, $event->term);
		return $report;
	}

	public function getExhaustionExplanation ()
	{
		return "Vyčerpání paliva";
	}

	public function formatExhaustionReport (Entities\Report $report)
	{
		return array();
	}

	public function getValue (Entities\Resource $resource)
	{
		return $resource->production * 3600;
	}
}
