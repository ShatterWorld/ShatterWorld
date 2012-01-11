<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;
use Nette\Caching\Cache;

class TradeRoutes extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Obchodní stezky';
	}

	public function getExplanation ()
	{
		return 'Zvýšení maximálního počtu prostředníků a rychlost obchodníků';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => 250 + max(0, $level - 1) * 150,
			'fuel' => 30 + max(0, $level - 2) * 50,
			'metal' => $level * 100
		);
	}

	public function getResearchTime ($level = 1)
	{
		return (4 + $level) * 60 * 60;
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
