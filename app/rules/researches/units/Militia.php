<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class Militia extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Domobrana';
	}

	public function getExplanation ()
	{
		return 'Síla domobrany';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => pow($level, 2) * 200,
			'stone' => pow($level, 2) * 200,
			'metal' => pow($level, 2) * 200
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

	public function afterResearch (Entities\Construction $construction){}

	public function getLevelCap (){
		return 3;
	}

	public function getCategory ()
	{
		return 'unit';
	}

	public function getValue ($level = 1)
	{
		return 100 + $level * 100;
	}
}

