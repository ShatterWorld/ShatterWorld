<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class Spy extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Špion';
	}

	public function getExplanation ()
	{
		return 'Síla špionů a rozvětky';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => pow($level, 2) * 500,
			'stone' => pow($level, 2) * 500,
			'metal' => pow($level, 2) * 500
		);
	}

	public function getResearchTime ($level = 1)
	{
		return (14 + $level) * 60 * 60;
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
		return 'unit';
	}

	public function getValue ($level = 1)
	{
		return 1000 + $level * 400;
	}
}

