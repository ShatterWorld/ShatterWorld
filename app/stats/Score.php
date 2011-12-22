<?php
namespace Stats;
use Entities;
use Nette\Diagnostics\Debugger;

class Score extends AbstractStat
{


	/**
	 * Get the score of the clan
	 * @param Entities\Clan
	 * @return int
	 */
	public function getClanScore (Entities\Clan $clan)
	{
		$models = $this->getContext()->model;
		$rules = $this-> getContext()->rules;

		$score = array();

		$score['size'] = 100 * $models->getFieldRepository()->getTerritorySize($clan);

		$score['researches'] = 0;
		foreach($models->getResearchRepository()->getResearched($clan) as $research){
			$score['researches'] += $rules->get('research', $research->type)->getValue($research->level);
		}

		$score['units'] = 0;
		foreach($models->getUnitRepository()->getClanUnits($clan) as $unit){
			$score['units'] += $rules->get('unit', $unit->type)->getValue() * $unit->count;
		}

		$score['quests'] = 0;
		foreach($models->getQuestRepository()->findCompleted($clan) as $quest){
			$score['quests'] += $rules->get('quest', $quest->type)->getValue($quest->level);
		}

		$score['facilities'] = 0;
		foreach($models->getFieldRepository()->getClanFacilities($clan) as $type => $level){
			$score['facilities'] += $rules->get('facility', $type)->getValue($level);
		}

		$score['resources'] = 0;
		foreach($models->getResourceRepository()->getResourcesArray($clan) as $type => $resource){
			$score['resources'] += $rules->get('resource', $type)->getValue() * $resource['balance'];
		}

		return $score;

	}

}
