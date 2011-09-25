<?php
namespace Rules\Units;
use Rules\AbstractRule;

class Militia extends AbstractRule implements IUnit
{
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
}