<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;
use Nette\Caching\Cache;

class TradeRoutes extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Hloubka obchodu';
	}

	public function getExplanation ()
	{
		return 'Počet prostředníků + rychlost obchodníků';
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

}
