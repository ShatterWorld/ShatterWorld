<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class Trading extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Obchod';
	}

	public function getExplanation ()
	{
		return 'Lepší obchodníci';
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
		$arr = $this->getContext()->model->getClanRepository()->getClanGraphCache();
		unset($arr[$construction->owner->id]);
	}

	public function getLevelCap ()
	{
		return 10;
	}

}
