<?php
namespace Rules\Units;
use Rules\AbstractRule;

class Militia extends AbstractRule implements IUnit
{
	public function getDescription ()
	{
		return 'Domobrana';
	}

	public function getAttack ()
	{
		return 0;
	}

	public function getDefense ()
	{
		return 1;
	}

	public function getSpeed ()
	{
		return 6 * 60;
	}

	public function getCapacity ()
	{
		return 20;
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
			'food' => 1 / 3600
		);
	}

	public function getTrainingTime ()
	{
		return 40 * 60;
	}

	public function getDifficulty ()
	{
		return array(
			'barracks' => 1
		);
	}

	public function getValue ()
	{
		return 0.7;
	}
}
