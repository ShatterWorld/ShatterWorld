<?php
namespace Rules\Facilities;
use Rules\AbstractRule;

class Barracks extends AbstractRule implements IConstructionFacility
{
	public function getDescription ()
	{
		return 'Kasárny';
	}

	public function getDependencies ()
	{
		return array();
	}

	public function getConstructionCost ($level = 1)
	{
		return array(
			'food' => $level * 150,
			'stone' => 100 + $level * 150,
			'metal' => 50 + $level * 100,
			'fuel' => 0 + max($level - 3, 0) * 50
		);
	}

	public function getConstructionTime ($level = 1)
	{
		return 20 * 60 + pow($level, 3) * 10 * 60;
	}

	public function getDemolitionCost ($from, $level = 0)
	{
		return array(
			'food' => 100 + $level * 20,
			'fuel' => 100
		);
	}

	public function getDemolitionTime ($from, $level = 0)
	{
		return 5*60 + ($from - $level) * 90;
	}

	public function getProduction ($level = 1)
	{
		return array(
			'food' => (-1 -log($level, 2)) / 3600,
		);
	}

	public function getCapacity ($level = 1)
	{
		return intval(1 + 2*$level + pow($level, 2)/4);
	}

	public function getDefenceBonus ($level = 1)
	{
		return 0.2;
	}

	public function getValue ($level = 1)
	{
		return 100 + $level * 20;
	}
}
