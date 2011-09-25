<?php
namespace Rules\Units;
use Rules\AbstractRule;

class Artillery extends AbstractRule implements IUnit
{
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
		return 240;
	}
	
	public function getCost ()
	{
		return array(
			'food' => 10,
			'metal' => 50
		);
	}
	
	public function getUpkeep ()
	{
		return array();
	}
	
	public function getTrainingTime ()
	{
		return 3600;
	}
	
	public function getDifficulty ()
	{
		return array(
			'barracks' => 1,
			'workshop' => 1
		);
	}
}