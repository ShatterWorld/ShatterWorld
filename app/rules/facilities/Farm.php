<?php
namespace Rules\Facilities;
use Rules\AbstractRule;

class Farm extends AbstractRule implements IFacility
{
	public function getDependencies ()
	{
		return array();
	}
	
	public function getCost ($level = 1)
	{
		return array(
			'food' => $level * 5,
			'stone' => max($level - 3, 0) * 5
		);
	}
	
	public function getProduction ($level = 1)
	{
		return array(
			'food' => $level
		);
	}
}