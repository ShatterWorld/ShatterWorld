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
			'food' => pow($level, 2) * 300,
			'stone' => pow($level, 2) * 300,
			'metal' => pow($level, 2) * 300
		);
	}

	public function getResearchTime ($level = 1)
	{
		return (5 + $level) * 60 * 60;
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

	public function getCategory ()
	{
		return 'administration';
	}

	public function getValue ($level = 1)
	{
		return 200 + $level * 70;
	}

}
