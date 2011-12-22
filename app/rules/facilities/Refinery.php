<?php
namespace Rules\Facilities;
use Rules\AbstractRule;

class Refinery extends AbstractRule implements IFacility
{
	public function getDescription ()
	{
		return 'Rafinérie';
	}

	public function getDependencies ()
	{
		return array();
	}

	public function getConstructionCost ($level = 1)
	{
		return array(
			'food' => 80 + max($level - 1, 0) * 100,
			'stone' => 80 + max($level - 1, 0) * 50,
			'metal' => 60 + max($level - 1, 0) * 70,
			'fuel' => 60 + max($level - 1, 0) * 50
		);
	}

	public function getConstructionTime ($level = 1)
	{
		return 10*60 + pow($level, 2) * 90;
	}

	public function getDemolitionCost ($from, $level = 0)
	{
		return array(
			'food' => 80 + ($from - $level) * 100,
			'stone' => 80 + ($from - $level) * 50,
			'metal' => 60 + ($from - $level) * 70,
			'fuel' => 60 + ($from - $level) * 50
		);
	}

	public function getDemolitionTime ($from, $level = 0)
	{
		return 5*60 + ($from - $level) * 90;
	}

	public function getProduction ($level = 1)
	{
		return array(
			'fuel' => $level / 100
		);
	}

	public function getDefenceBonus ($level = 1)
	{
		return -0.2;
	}

	public function getValue ($level = 1)
	{
		return 100 + $level * 20;
	}
}
