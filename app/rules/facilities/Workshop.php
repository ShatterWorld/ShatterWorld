<?php
namespace Rules\Facilities;
use Rules\AbstractRule;

class Workshop extends AbstractRule implements IConstructionFacility
{
	public function getDescription ()
	{
		return 'Dílna';
	}

	public function getDependencies ()
	{
		return array();
	}

	public function getConstructionCost ($level = 1)
	{
		return array(
			'food' => $level * 100,
			'stone' => 100 + $level * 150,
			'metal' => 100 + $level * 100,
			'fuel' => 100 + max($level - 3, 0) * 50
		);
	}

	public function getConstructionTime ($level = 1)
	{
		return 10*60 + pow($level, 2) * 90;
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
			'food' => -4*$level / 3600
		);
	}

	public function getCapacity ($level = 1)
	{
		return intval(floor(pow($level + 2, 2) / 7));
	}

	public function getDefenceBonus ($level = 1)
	{
		return 0;
	}

	public function getValue ($level = 1)
	{
		return 150 + $level * 20;
	}
}
