<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class ColonisationEfficiency extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Efektivita kolonizace';
	}

	public function getExplanation ()
	{
		return 'Lepší osadníci, nižší náklady, rychlejší osídlení';
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
