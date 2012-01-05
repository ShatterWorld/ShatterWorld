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
			'food' => 150,
			'metal' => 200
		);
	}

	public function getUpkeep ()
	{
		return array(
			'food' => 15 / 3600,
			'fuel' => 5 / 3600
		);
	}

	public function getTrainingTime ()
	{
		return 120*60;
	}

	public function getDifficulty ()
	{
		return array(
			'barracks' => 2,
			'workshop' => 6
		);
	}

	public function getValue ()
	{
		return 7;
	}
}
