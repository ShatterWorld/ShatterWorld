<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;
use Nette\Caching\Cache;

class Reconnaissance extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Viditelnost';
	}

	public function getExplanation ()
	{
		return 'Okruh polí, které klan kolem sebe vidí';
	}

	public function getCost ($level = 1)
	{
		return array(
			'stone' => $level * 70,
			'metal' => $level * 200,
			'fuel' => $level * 35,
			'food' => $level * 120
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
