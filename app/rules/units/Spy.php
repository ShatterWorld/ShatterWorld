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
		return 150;
	}

	public function getCapacity ()
	{
		return 0;
	}

	public function getCost ()
	{
		return array(
			'food' => 1800,
			'metal' => 1500
		);
	}

	public function getUpkeep ()
	{
		return array(
			'food' => 0.01
		);
	}

	public function getTrainingTime ()
	{
		return 16000;
	}

	public function getDifficulty ()
	{
		return array(
			'barracks' => 1
		);
	}
}
