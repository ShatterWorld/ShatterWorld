<?php
namespace Rules\Units;
use Rules\AbstractRule;

class HeavyVehicle extends AbstractRule implements IUnit
{
	public function getDescription ()
	{
		return 'Obrněné vozidlo';
	}

	public function getAttack ()
	{
		return 7;
	}
	
	public function getDefense ()
	{
		return 5;
	}
	
	public function getSpeed ()
	{
		return 150;
	}
	
	public function getCapacity ()
	{
		return 60;
	}
	
	public function getCost ()
	{
		return array(
			'food' => 180,
			'metal' => 150
		);
	}
	
	public function getUpkeep ()
	{
		return array(
			'food' => 0.02,
			'fuel' => 0.03
		);
	}
	
	public function getTrainingTime ()
	{
		return 7200;
	}
	
	public function getDifficulty ()
	{
		return array(
			'barracks' => 2,
			'workshop' => 6
		);
	}
}