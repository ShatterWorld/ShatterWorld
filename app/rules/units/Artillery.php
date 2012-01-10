<?php
namespace Rules\Units;
use Rules\AbstractRule;

class Artillery extends AbstractRule implements IUnit
{
	public function getDescription ()
	{
		return 'Artilérie';
	}

	public function getAttack ()
	{
		return 5;
	}

	public function getDefense ()
	{
		return 1;
	}

	public function getSpeed ()
	{
		return 10 * 60;
	}

	public function getCapacity ()
	{
		return 5;
	}

	public function getCost ()
	{
		return array(
			'food' => 70,
			'metal' => 60
		);
	}

	public function getUpkeep ()
	{
		return array(
			'food' => 5 / 3600,
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
			'barracks' => 1,
			'workshop' => 1
		);
	}

	public function getValue ()
	{
		return 3;
	}
}
