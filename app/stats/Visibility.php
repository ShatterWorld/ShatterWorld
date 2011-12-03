<?php
namespace Stats;
use Entities;

class Visibility extends AbstractStat
{
	/**
	 * Returns how many fields in the distance can the clan see from its territory
	 * @param Entities\Clan
	 * @return int
	 */
	public function getRadius (Entities\Clan $clan)
	{
		$baseReconnaissance = $this->getContext()->params['game']['stats']['baseLOS'];
		$level = $this->getContext()->model->getResearchRepository()->getResearchLevel($clan, 'reconnaissance');
		return $baseReconnaissance + $level;
	}
}