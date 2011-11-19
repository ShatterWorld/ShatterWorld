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

	public function getCost ($level = 1)
	{
		return array(
			'stone' => pow($level, 2) * 500
		);
	}

	public function getResearchTime ($level = 1)
	{
		return $level * 36000;
	}

	public function getDependencies ()
	{
		return array(
			'researchEfficiency' => 1
		);
	}

	public function afterResearch (Entities\Construction $construction)
	{
		$this->getContext()->model->getResourceService()->recalculateProduction($construction->owner, $construction->term);
	}

	public function getLevelCap ()
	{
		return 10;
	}

	public function getConflicts ()
	{
		return array();
	}
}
