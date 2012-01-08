<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class LightVehicle extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Lehké vozidlo';
	}

	public function getExplanation ()
	{
		return 'Vylepšení technologií lehkého vozidla';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => pow($level, 2) * 300,
			'stone' => pow($level, 2) * 300,
			'metal' => pow($level, 2) * 300,
			'fuel' => pow($level, 2) * 300
		);
	}

	public function getResearchTime ($level = 1)
	{
		return (8 + $level) * 60 * 60;
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
		return 600 + $level * 300;
	}
}

