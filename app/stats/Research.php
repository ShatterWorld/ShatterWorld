<?php
namespace Stats;
use Entities;

class Research extends AbstractStat
{
	/**
	 * Returns research efficiency
	 * @param Entities\Clan
	 * @return float
	 */
	public function getEfficiency (Entities\Clan $clan)
	{
		$level = $this->getContext()->model->getResearchRepository()->getResearchLevel($clan, 'researchEfficiency');
		return 1 - pow($level, 2)/100;
	}
	
	public function getCost (Entities\Clan $clan, $research, $level)
	{
		$rule = $this->getContext()->rules->get('research', $research);
		$price = $rule->getCost($level);
		foreach ($price as $resource => $amount) {
			$price[$resource] = $amount * $this->getEfficiency($clan);
		}
		return $price;
	}
	
	public function getTime (Entities\Clan $clan, $research, $level)
	{
		$rule = $this->getContext()->rules->get('research', $research);
		$time = $rule->getResearchTime($level);
		return $time * $this->getEfficiency($clan);
	}
}