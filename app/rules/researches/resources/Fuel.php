<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class Fuel extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Těžba paliva';
	}

	public function getExplanation ()
	{
		return 'Vyšší produkce paliva z rafinérií';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => pow($level, 2) * 600,
			'stone' => pow($level, 2) * 600,
			'metal' => pow($level, 2) * 600,
			'fuel' => pow($level, 2) * 800
		);
	}

	public function getResearchTime ($level = 1)
	{
		return (19 + $level) * 60 * 60;
	}

	public function getDependencies ()
	{
		return array(
			'researchEfficiency' => 2,
			'storage' => 1
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
		return 5;
	}

	public function getCategory ()
	{
		return 'resource';
	}

	public function getValue ($level = 1)
	{
		return 300 + $level * 200;
	}
}
