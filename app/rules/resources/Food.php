<?php
namespace Rules\Resources;
use Entities;
use Rules\AbstractRule;
use Nette\Diagnostics\Debugger;
use ReportItem;

class Food extends AbstractRule implements IExhaustableResource
{
	public function getDescription ()
	{
		return 'Jídlo';
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
			return (isset($bUpkeep['food']) ? $bUpkeep['food'] : 0)
				- (isset($aUpkeep['food']) ? $aUpkeep['food'] : 0);
		});

		$production = -$resources['food']['production'];

		foreach ($units as $unit) {
			if ($production <= 0) {
				break;
			}
			$upkeep = $rules->get('unit', $unit->type)->getUpkeep();
			if ($upkeep['food'] <= 0) {
				continue;
			}
			$unkept = min($unit->count, ceil($production / $upkeep['food']));
			$production -= $upkeep['food'] * $unkept;
			$report['units'][$unit->type] = $unkept;
			$this->getContext()->model->unitService->removeUnits($clan, $unit->location, array($unit->type => $unkept), $event->term, FALSE);
		}
		$this->getContext()->model->resourceService->recalculateProduction($event->owner, $event->term);
		return $report;
	}

	public function getExhaustionExplanation ()
	{
		return "Hladomor";
	}

	public function formatExhaustionReport (Entities\Report $report)
	{
		$data = $report->data;

		$message = array(ReportItem::create('text', 'Došlo k vyprázdnění skladů s jídlem a některé jednotky zemřely hladem.'));
		$message[] = ReportItem::create('unitGrid', array($data['units']))->setHeading('Ztráty');
		return $message;
	}

	public function getValue (Entities\Resource $resource)
	{
		return $resource->production * 1800;
	}
}
