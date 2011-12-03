<?php
namespace Stats;
use Entities;

class Colonisation extends AbstractStat
{
	/**
	 * Calculate the colonisation cost coefficient
	 * @param Entities\Field
	 * @param Entities\Clan
	 * @return int
	 */
	protected function getCoefficient (Entities\Field $target, Entities\Clan $clan)
	{
		$level = $this->getContext()->model->getResearchRepository()->getResearchLevel($clan, 'colonisationEfficiency');
		$distance = $this->getContext()->model->getFieldRepository()->calculateDistance($clan->getHeadquarters(), $target);
		$count = $this->getContext()->model->getFieldRepository()->getTerritorySize($clan) + $this->getContext()->model->getConstructionRepository()->getColonisationCount($clan);
		return $distance * $count * (1 - (pow($level, 2) / 100));
	}
	
	/**
	 * Calculate the time needed to colonise a field
	 * @param Entities\Field
	 * @param Entities\Clan
	 * @return int
	 */
	public function getTime (Entities\Field $target, Entities\Clan $clan)
	{
		$base = $this->getContext()->params['game']['stats']['baseColonisationTime'];
		$coefficient = $this->getCoefficient($target, $clan);
		return $base * $coefficient;
	}
	
	/**
	 * Calculate the costs needed to colonise a field
	 * @param Entities\Field
	 * @param Entities\Clan
	 * @return int
	 */
	public function getCost (Entities\Field $target, Entities\Clan $clan)
	{
		$coefficient = $this->getCoefficient($target, $clan);
		return array(
			'food' => 10 * $coefficient,
			'stone' => 20 * $coefficient,
			'metal' => 20 * $coefficient
		);
	}
}