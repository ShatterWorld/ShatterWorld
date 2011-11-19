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

	public function getConflicts ()
	{
		return array();
	}

	public function afterResearch (Entities\Construction $construction){}

	public function getLevelCap (){
		return 10;
	}

}
