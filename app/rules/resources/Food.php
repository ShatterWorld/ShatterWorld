<?php
namespace Rules\Resources;
use Rules\AbstractRule;
use Nette\Diagnostics\Debugger;

class Food extends AbstractRule implements IResource
{
	public function getDescription ()
	{
		return 'Jídlo';
	}

	public function getInitialAmount ()
	{
		return 500;
	}

	public function processExhaustion ($clan)
	{
		Debugger::barDump($a="pE");
		//count and kill

		$resources = $this->getContext()->model->getResourceRepository()->getResourcesArray($clan);

		//cout new ones
		$units = $this->getContext()->model->getUnitRepository()->getClanUnits($clan);

		//sort units
		$rules = $this->getContext()->rules;
		usort($units, function ($a, $b) use ($rules){
			$aUpkeep = $rules->get('unit', $a->type)->getUpkeep();
			$bUpkeep = $rules->get('unit', $b->type)->getUpkeep();
			if ($aUpkeep == $bUpkeep) return 0;
			return ($aUpkeep < $bUpkeep) ? -1 : 1;
		});


		//determine
		$production = -$resources['food']['production'];
		$time = floor($resources['food']['balance'] / $production);

		foreach($units as $unit){
			if ($production <= 0){
				break;
			}

			$upkeep = $rules->get('unit', $unit->type)->getUpkeep();

			if ($upkeep['food'] <= 0){
				continue;
			}

			Debugger::barDump($unit->count);
			Debugger::barDump(floor($production / $upkeep['food']));
			$count = min($unit->count, floor($production / $upkeep['food']));
			$production -= $upkeep['food'] * $count;

			//kill
			$this->getContext()->model->getUnitService()->removeUnits($clan, $unit->location, array($unit->type, $count));
		}


	}

}
