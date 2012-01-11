<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class HeavyVehicle extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Obrněné vozidlo';
	}

	public function getExplanation ()
	{
		return 'Vylepšení technologií obrněného vozidla';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => $level * 150,
			'fuel' => $level * 120,
			'metal' => $level * 300,
		);
	}

	public function getResearchTime ($level = 1)
	{
		return (10 + $level) * 60 * 60;
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
		return 700 + $level * 300;
	}
}

