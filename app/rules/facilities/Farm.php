<?php
namespace Rules\Facilities;
use Rules\AbstractRule;

class Farm extends AbstractRule implements IFacility
{
	public function getDescription ()
	{
		return 'Farma';
	}

	public function getDependencies ()
	{
		return array();
	}

	public function getConstructionCost ($level = 1)
	{
		return array(
			'food' => $level * 40,
			'stone' => max($level - 3, 0) * 20
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
			'food' => $level / 100
		);
	}

	public function getDefenceBonus ()
	{
		return 0;
	}
}
