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

	public function getExplanation ()
	{
		return 'Nové postupy ukládání surovin';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => 150 + max(0, $level - 1) * 100,
			'fuel' => $level * 25,
			'metal' => $level * 75,
			'stone' => $level * 100
		);
	}

	public function getResearchTime ($level = 1)
	{
		return (2 + $level) * 60 * 60;
	}

	public function getDependencies ()
	{
		return array();
	}

	public function getConflicts ()
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

	public function getCategory ()
	{
		return 'economy';
	}

	public function getValue ($level = 1)
	{
		return 100 + $level * 100;
	}

}
