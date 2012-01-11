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
		return 'Nižší náklady a rychlejší kolonizace';
	}

	public function getCost ($level = 1)
	{
		return array(
			'stone' => $level * 170,
			'metal' => $level * 250,
			'fuel' => $level * 80,
			'food' => $level * 150
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
		return 100 + $level * 50;
	}

}
