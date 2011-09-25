<?php
namespace Rules\Units;
use Rules\AbstractRule;

class LightVehicle extends AbstractRule implements IUnit
{
	public function getAttack ()
	{
		return 4;
	}
	
	public function getDefense ()
	{
		return 3;
	}
	
	public function getSpeed ()
	{
		return 90;
	}
	
	public function getCost ()
	{
		return array(
			'food' => 30,
			'metal' => 50
		);
	}
	
	public function getUpkeep ()
	{
		return array(
			'food' => 0.01,
			'fuel' => 0.01
		);
	}
	
	public function getTrainingTime ()
	{
		return 3600;
	}
	
	public function getDifficulty ()
	{
		return array(
			'barracks' => 2,
			'workshop' => 2
		);
	}
}