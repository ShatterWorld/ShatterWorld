<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class ConstructionTechnology extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Stavební technologie';
	}

	public function getExplanation ()
	{
		return 'Rychlejší stavba, nižší náklady';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => pow($level, 2) * 150,
			'stone' => pow($level, 2) * 150,
			'metal' => pow($level, 2) * 150
		);
	}

	public function getResearchTime ($level = 1)
	{
		return $level * 24000;
	}

	public function getDependencies ()
	{
		return array(
			'researchEfficiency' => 1
		);
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
