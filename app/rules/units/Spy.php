<?php
namespace Rules\Units;
use Rules\AbstractRule;

class Spy extends AbstractRule implements IUnit
{
	public function getDescription ()
	{
		return 'Špion';
	}

	public function getAttack ()
	{
		return 0;
	}

	public function getDefense ()
	{
		return 0;
	}

	public function getSpeed ()
	{
		return 3 * 60;
	}

	public function getCapacity ()
	{
		return 0;
	}

	public function getCost ()
	{
		return array(
			'food' => 150,
			'metal' => 150
		);
	}

	public function getUpkeep ()
	{
		return array(
			'food' => 2 / 3600
		);
	}

	public function getTrainingTime ()
	{
		return 240 * 60;
	}

	public function getDifficulty ()
	{
		return array(
			'barracks' => 1
		);
	}

	public function getValue ()
	{
		return 1.5;
	}
}
