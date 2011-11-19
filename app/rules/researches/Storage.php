<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class Storage extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Skladování';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => pow($level, 2) * 250,
			'stone' => pow($level, 2) * 250,
			'metal' => pow($level, 2) * 250,
		);
	}

	public function getResearchTime ($level = 1)
	{
		return $level * 18000;
	}

	public function getDependencies ()
	{
		return array();
	}

	public function afterResearch (Entities\Construction $construction)
	{
		$this->getContext()->model->getResourceService()->recalculateStorage($construction->owner, $construction->term);
	}

	public function getLevelCap (){
		return 10;
	}

	public function getConflicts ()
	{
		return array();
	}
}
