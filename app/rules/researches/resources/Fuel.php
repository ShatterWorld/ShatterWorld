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
			'food' => 350 + max(0, $level - 1) * 300,
			'stone' => 350 + max(0, $level - 1) * 300,
			'metal' => 350 + max(0, $level - 1) * 300,
			'fuel' => 300 + max(0, $level - 1) * 300
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
