<?php
namespace Rules\Facilities;
use Rules\AbstractRule;

class Refinery extends AbstractRule implements IFacility
{
	public function getDependencies ()
	{
		return array();
	}
	
	public function getConstructionCost ($level = 1)
	{
		return array(
			'stone' => $level * 10,
			'metal' => ($level - 1) * 10 
		);
	}
	
	public function getConstructionTime ($level = 1)
	{
		return pow($level, 2) * 90;
	}
	
	public function getDemolitionCost ($from, $level = 0)
	{
		return array();
	}
	
	public function getDemolitionTime ($from, $level = 0)
	{
		return ($from - $level) * 90;
	}
	
	public function getProduction ($level = 1)
	{
		return array(
			'fuel' => $level / 100
		);
	}
}