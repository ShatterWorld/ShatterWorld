<?php
namespace Rules\Units;
use Rules\AbstractRule;

class Footman extends AbstractRule implements IUnit
{
	public function getDescription ()
	{
		return 'Pěšák';
	}

	public function getAttack ()
	{
		return 2;
	}

	public function getDefense ()
	{
		return 3;
	}

	public function getSpeed ()
	{
		return 5 * 60;
	}

	public function getCapacity ()
	{
		return 30;
	}

	public function getCost ()
	{
		return array(
			'food' => 40,
			'metal' => 30
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
		return 60 * 60;
	}

	public function getDifficulty ()
	{
		return array(
			'barracks' => 1
		);
	}

	public function getValue ()
	{
		return 1;
	}
}
