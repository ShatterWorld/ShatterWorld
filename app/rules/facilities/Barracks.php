<?php
namespace Rules\Facilities;
use Rules\AbstractRule;

class Barracks extends AbstractRule implements IFacility
{
	public function getDependencies ()
	{
		return array();
	}
	
	public function getConstructionCost ($level = 1)
	{
		return array(
			'stone' => $level * 10,
			'metal' => ($level - 1) * 5
		);
	}
	
	public function getConstructionTime ($level = 1)
	{
		return pow($level, 2) * 90;
	}
	
	public function getProduction ($level = 1)
	{
		return array();
	}
}