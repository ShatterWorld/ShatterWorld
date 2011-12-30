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
		return 1;
	}

	public function getDefense ()
	{
		return 1;
	}

	public function getSpeed ()
	{
		return 120;
	}

	public function getCapacity ()
	{
		return 20;
	}

	public function getCost ()
	{
		return array(
			'food' => 30,
			'metal' => 5
		);
	}

	public function getUpkeep ()
	{
		return array(
			'food' => 0.001
		);
	}

	public function getTrainingTime ()
	{
		return 1200;
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
