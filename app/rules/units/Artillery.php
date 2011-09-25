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
}