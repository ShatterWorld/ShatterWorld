<?php
namespace Rules\Facilities;
use Rules\AbstractRule;

class Mine extends AbstractRule implements IFacility
{
	public function getDependencies ()
	{
		return array();
	}
	
	public function getCost ($level = 1)
	{
		return array(
			'stone' => $level * 10,
			'metal' => ($level - 1) * 5 
		);
	}
	
	public function getProduction ($level = 1)
	{
		return array(
			'metal' => $level
		);
	}
}