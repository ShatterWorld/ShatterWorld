<?php
namespace Stats;
use Entities;
use Nette\Diagnostics\Debugger;

class Construction extends AbstractStat
{
	public function getAvailableFacilities (Entities\Clan $clan)
	{
		$result = array();
		foreach ($this->getContext()->rules->getAll('facility') as $name => $facility) {
			$depsSatisfied = TRUE;
			foreach ($facility->getDependencies() as $dep => $level) {
				if ($this->getContext()->model->getResearchRepository()->getResearchLevel($clan, $dep) < $level){
					$depsSatisfied = FALSE;
					break;
				}
			}
			if ($depsSatisfied && $name !== 'headquarters') {
				$result[] = $name;
			}
		}
		return $result;
	}

	/**
	 * Returns the construction coefficient
	 * @param Entities\Clan
	 * @return float
	 */
	protected function getCoefficient (Entities\Clan $clan)
	{
		$level = $this->getContext()->model->getResearchRepository()->getResearchLevel($clan, 'constructionTechnology');
		return 1 - pow($level, 2)/100;
	}

	public function getConstructionCost (Entities\Clan $clan, $facility, $level = 1)
	{
		$rule = $this->getContext()->rules->get('facility', $facility);
		$price = $rule->getConstructionCost($level);
		foreach ($price as $resource => $amount) {
			$price[$resource] = $amount * $this->getCoefficient($clan);
		}
		return $price;
	}

	public function getRepairCost (Entities\Clan $clan, $facility, $level = 1)
	{
		return array_map(function ($value) { return (floor($value / 2)); }, $this->getConstructionCost($clan, $facility, $level));
	}

	public function getDemolitionCost (Entities\Clan $clan, $facility, $from, $level = 0)
	{
		$rule = $this->getContext()->rules->get('facility', $facility);
		$price = $rule->getDemolitionCost($from, $level);
		foreach ($price as $resource => $amount) {
			$price[$resource] = $amount * $this->getCoefficient($clan);
		}
		return $price;
	}

	public function getConstructionTime (Entities\Clan $clan, $facility, $level = 1)
	{
		$rule = $this->getContext()->rules->get('facility', $facility);
		$time = $rule->getConstructionTime($level);
		return $time * $this->getCoefficient($clan);
	}

	public function getRepairTime (Entities\Clan $clan, $facility, $level = 1)
	{
		return floor($this->getConstructionTime($clan, $facility, $level) / 2);
	}

	public function getDemolitionTime (Entities\Clan $clan, $facility, $from, $level = 0)
	{
		$rule = $this->getContext()->rules->get('facility', $facility);
		$time = $rule->getDemolitionTime($from, $level);
		return $time * $this->getCoefficient($clan);
	}

	public function getAbandonmentTime ()
	{
		return $this->getContext()->params['game']['stats']['baseAbandonmentTime'];
	}
}
