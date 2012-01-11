<?php
namespace Stats;
use Entities;
use Nette\Diagnostics\Debugger;

class Exploration extends AbstractStat
{

	/**
	 * Returns the exploration bonus <0;inf)
	 * @param Entities\Clan
	 * @return float
	 */
	public function getBonus (Entities\Clan $clan)
	{
		$param = $this->getContext()->params['game']['stats'];
		$level = $this->getContext()->model->getResearchRepository()->getResearchLevel($clan, 'explorationEfficiency');
		$bonus = rand(($param['minExplorationCoefficient'] + $level/4) * 100, ($param['maxExplorationCoefficient'] + $level/2) * 100) / 100;
		return $bonus;
	}
}
