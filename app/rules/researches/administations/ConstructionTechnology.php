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
		return 'Nižší náklady a rychlejší stavba';
	}

	public function getCost ($level = 1)
	{
		return array(
			'stone' => $level * 100,
			'metal' => $level * 200,
			'food' => $level * 150
		);
	}

	public function getResearchTime ($level = 1)
	{
		return (5 + $level) * 60 * 60;
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

	public function getCategory ()
	{
		return 'administration';
	}

	public function getValue ($level = 1)
	{
		return 50 + $level * 20;
	}
}
