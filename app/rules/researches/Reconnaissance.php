<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

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
			'fuel' => pow($level, 2) * 700,
			'stone' => pow($level, 2) * 200
		);
	}

	public function getResearchTime ($level = 1)
	{
		return $level * 36000;
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
		$arr = $this->getContext()->model->getClanRepository()->getClanGraphCache();
		unset($arr[$construction->owner->id]);
	}

	public function getLevelCap ()
	{
		return 10;
	}

}
