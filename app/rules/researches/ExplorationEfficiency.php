<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class ExplorationEfficiency extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Efektivita průzkumu';
	}

	public function getExplanation ()
	{
		return 'Rychlejší průzkum, nižší náklady etc';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => pow($level, 3) * 300,
			'stone' => pow($level, 3) * 300,
			'metal' => pow($level, 3) * 300
		);
	}

	public function getResearchTime ($level = 1)
	{
		return $level * 24000;
	}

	public function getDependencies ()
	{
		return array();
	}

	public function getConflicts ()
	{
		return array();
	}

	public function afterResearch (Entities\Construction $construction){}

	public function getLevelCap (){
		return 10;
	}

}
