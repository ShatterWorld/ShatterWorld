<?php
namespace Rules\Facilities;
use Rules\AbstractRule;

class Headquarters extends AbstractRule implements IFacility
{
	public function getDescription ()
	{
		return 'Velitelství';
	}

	public function getDependencies ()
	{
		return array();
	}

	public function getConstructionCost ($level = 1)
	{
		return array();
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

	public function getDefenceBonus ($level = 1)
	{
		return 0.5;
	}
}
