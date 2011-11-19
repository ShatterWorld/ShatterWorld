<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class Food extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Farmaření';
	}

	public function getExplanation ()
	{
		return 'Zvýšení industrializace farem';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => pow($level, 2) * 500
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
		$this->getContext()->model->getResourceService()->recalculateProduction($construction->owner, $construction->term);
	}

	public function getLevelCap ()
	{
		return 10;
	}

}
