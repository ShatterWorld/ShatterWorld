<?php
namespace Rules\Units;
use Rules\AbstractRule;

class LightVehicle extends AbstractRule implements IUnit
{
	public function getAttack ()
	{
		return 3;
	}
	
	public function getDefense ()
	{
		return 2;
	}
	
	public function getSpeed ()
	{
		return 90;
	}
}