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
		return 'Vyšší produkce jídla z farem';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => pow($level, 2) * 600,
			'stone' => pow($level, 2) * 400,
			'metal' => pow($level, 2) * 400,
			'fuel' => pow($level, 2) * 400
		);
	}

	public function getResearchTime ($level = 1)
	{
		return (9 + $level) * 60 * 60;
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

	public function getCategory ()
	{
		return 'resource';
	}

	public function getValue ($level = 1)
	{
		return 200 + $level * 150;
	}
}
