<?php
namespace Stats;
use Entities;

class Trading extends AbstractStat
{
	/**
	 * Returns the number of mediators the offer can go through
	 * @param Entities\Clan
	 * @return int
	 */
	public function getRadius (Entities\Clan $clan)
	{
		$baseRadius = $this->getContext()->params['game']['stats']['baseTradingRadius'];
		$level = $this->getContext()->model->getResearchRepository()->getResearchLevel($clan, 'tradeRoutes');
		return $baseRadius + 2 * $level;
	}

	/**
	 * Returns the speed of merchants
	 * @param Entities\Clan
	 * @return int
	 */
	public function getMerchantSpeed (Entities\Clan $clan)
	{
		$baseMerchantSpeed = $this->getContext()->params['game']['stats']['baseMerchantSpeed'];
		$level = $this->getContext()->model->getResearchRepository()->getResearchLevel($clan, 'tradeRoutes');
		return $baseMerchantSpeed * (10 - $level) / 10;
	}

	/**
	 * Returns the % profit of the clan
	 * @param Entities\Clan
	 * @return float
	 */
	public function getProfit (Entities\Clan $clan)
	{
		$baseTradeProfit = $this->getContext()->params['game']['stats']['baseTradeProfit'];
		$level = $this->getContext()->model->getResearchRepository()->getResearchLevel($clan, 'tradeProfit');
		return $baseTradeProfit * pow($level, 2);
	}
}
