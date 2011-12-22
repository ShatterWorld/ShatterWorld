<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;
use Nette\Caching\Cache;

class Reconnaissance extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Znalost okolí';
	}

	public function getExplanation ()
	{
		return 'Okruh polí, které je viditelný kolem klanu';
	}

	public function getCost ($level = 1)
	{
		return array(
			'fuel' => pow($level, 2) * 300,
			'stone' => pow($level, 2) * 200,
			'metal' => pow($level, 2) * 100
		);
	}

	public function getResearchTime ($level = 1)
	{
		return (7 + $level) * 60 * 60;
	}

	public function getDependencies ()
	{
		return array(
			'researchEfficiency' => 1,
		);
	}

	public function getConflicts ()
	{
		return array();
	}

	public function afterResearch (Entities\Construction $construction)
	{
		$arr = $this->getContext()->model->getFieldRepository()->getVisibleFieldsCache();
		unset($arr[$construction->owner->id]);
		$arr = $this->getContext()->model->getClanRepository()->getVisibleClansCache();
		unset($arr[$construction->owner->id]);

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
		return 'administration';
	}

	public function getValue ($level = 1)
	{
		return 200 + $level * 100;
	}

}
