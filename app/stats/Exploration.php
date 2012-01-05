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
		$randomBonus = rand($param['minExplorationCoefficient'] * 100, $param['maxExplorationCoefficient'] * 100) / 100;
		$level = $this->getContext()->model->getResearchRepository()->getResearchLevel($clan, 'explorationEfficiency');
		return (1 + $level/2) * $randomBonus;
	}
}
