<?php
namespace Rules\Facilities;
use Rules\AbstractRule;

class Workshop extends AbstractRule implements IConstructionFacility
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
		return array();
	}
	
	public function getCapacity ($level = 1)
	{
		return floor(pow($level + 2, 2) / 7);
	}
}