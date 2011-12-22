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
		return 100;
	}

	public function getCapacity ()
	{
		return 30;
	}

	public function getCost ()
	{
		return array(
			'food' => 60,
			'metal' => 30
		);
	}

	public function getUpkeep ()
	{
		return array(
			'food' => 0.005
		);
	}

	public function getTrainingTime ()
	{
		return 45*60;
	}

	public function getDifficulty ()
	{
		return array(
			'barracks' => 1
		);
	}
}
