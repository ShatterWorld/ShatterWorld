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
			'food' => $level * 120,
			'fuel' => $level * 100,
			'metal' => $level * 230,
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

