<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class ResearchEfficiency extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Efektivita výzkumu';
	}

	public function getExplanation ()
	{
		return 'Nové vybavení laboratoří';
	}

	public function getCost ($level = 1)
	{
		return array(
			'stone' => pow($level, 2) * 500,
			'metal' => pow($level, 2) * 500,
			'fuel' => pow($level, 2) * 100
		);
	}

	public function getResearchTime ($level = 1)
	{
		return (6 + $level) * 60 * 60;
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
		return 50 + $level * 20;
	}

}
