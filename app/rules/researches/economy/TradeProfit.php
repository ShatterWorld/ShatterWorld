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
			'food' => 250 + max(0, $level - 1) * 150,
			'metal' => $level * 100,
			'stone' => $level * 30
		);
	}

	public function getResearchTime ($level = 1)
	{
		return (9 + $level) * 60 * 60;
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

	public function getValue ($level = 1)
	{
		return 100 + $level * 100;
	}

}
