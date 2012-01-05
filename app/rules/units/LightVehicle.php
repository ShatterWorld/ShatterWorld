<?php
namespace Rules\Units;
use Rules\AbstractRule;

class LightVehicle extends AbstractRule implements IUnit
{
	public function getDescription ()
	{
		return 'Lehké vozidlo';
	}

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

	public function getCapacity ()
	{
		return 80;
	}

	public function getCost ()
	{
		return array(
			'food' => 100,
			'metal' => 150
		);
	}

	public function getUpkeep ()
	{
		return array(
			'food' => 10 / 3600,
			'fuel' => 3 / 3600
		);
	}

	public function getTrainingTime ()
	{
		return 90 * 60;
	}

	public function getDifficulty ()
	{
		return array(
			'barracks' => 2,
			'workshop' => 2
		);
	}

	public function getValue ()
	{
		return 5;
	}
}
