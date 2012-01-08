<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class Artillery extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Artilérie';
	}

	public function getExplanation ()
	{
		return 'Vylepšení technologií artilérie';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => pow($level, 2) * 400,
			'stone' => pow($level, 2) * 400,
			'metal' => pow($level, 2) * 400
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
		return 3;
	}

	public function getCategory ()
	{
		return 'unit';
	}

	public function getValue ($level = 1)
	{
		return 500 + $level * 300;
	}
}

