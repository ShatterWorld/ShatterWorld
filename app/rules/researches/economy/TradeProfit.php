<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;
use Nette\Caching\Cache;

class TradeProfit extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Profit z obchodu';
	}

	public function getExplanation ()
	{
		return 'Procentuální zisk ze zboží, které přejde přes tvoje území';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => pow($level, 2) * 500,
			'fuel' => $level > 3 ? pow($level, 2) * 350 : 0
		);
	}

	public function getResearchTime ($level = 1)
	{
		return $level * 36000;
	}

	public function getDependencies ()
	{
		return array(
			'storage' => 1
		);
	}

	public function getConflicts ()
	{
		return array();
	}

	public function afterResearch (Entities\Construction $construction)
	{
		$cache = $this->getContext()->model->getClanRepository()->getClanGraphCache();
		$id = $construction->owner->id;
		$cache->clean(array(
			Cache::TAGS => array("observer/$id"),
		));
	}

	public function getLevelCap ()
	{
		return 10;
	}

	public function getCategory ()
	{
		return 'economy';
	}

}
