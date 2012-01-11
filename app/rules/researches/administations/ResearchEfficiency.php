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
		return 'Levnější a rychlejší výzkum';
	}

	public function getCost ($level = 1)
	{
		return array(
			'stone' => $level * 200,
			'metal' => $level * 200,
			'fuel' => $level * 40,
			'food' => $level * 150
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
