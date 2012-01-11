<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class Stone extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Těžba kamene';
	}

	public function getExplanation ()
	{
		return 'Vyšší produkce kamene z lomů';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => 250 + max(0, $level - 1) * 300,
			'stone' => 200 + max(0, $level - 1) * 300,
			'metal' => 250 + max(0, $level - 1) * 300,
			'fuel' => 250 + max(0, $level - 1) * 300
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
		return 200 + $level * 150;
	}
}
