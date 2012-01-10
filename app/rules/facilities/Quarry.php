<?php
namespace Rules\Facilities;
use Rules\AbstractRule;

class Quarry extends AbstractRule implements IFacility
{
	public function getDescription ()
	{
		return 'Kamenolom';
	}

	public function getDependencies ()
	{
		return array();
	}

	public function getConstructionCost ($level = 1)
	{
		return array(
			'food' => 40 + max($level - 1, 0) * 40,
			'stone' => 80 + max($level - 1, 0) * 80,
			'metal' => 30 + max($level - 1, 0) * 30,
			'fuel' => 0 + max($level - 5, 0) * 50
		);
	}

	public function getConstructionTime ($level = 1)
	{
		return 6 * 60 + pow($level, 3) * 2 * 60;
	}

	public function getDemolitionCost ($from, $level = 0)
	{
		return array(
			'food' => 40 + ($from - $level) * 40,
			'stone' => 80 + ($from - $level) * 80,
			'metal' => 30 + ($from - $level) * 30,
			'fuel' => 0 + ($from - $level) * 50
		);
	}

	public function getDemolitionTime ($from, $level = 0)
	{
		return 3*60 + ($from - $level) * 90;
	}

	public function getProduction ($level = 1)
	{
		return array(
			'food' => -7*$level / 3600,
			'stone' => 15*$level / 3600
		);
	}

	public function getDefenceBonus ($level = 1)
	{
		return 0;
	}

	public function getValue ($level = 1)
	{
		return 50 + $level * 20;
	}
}
