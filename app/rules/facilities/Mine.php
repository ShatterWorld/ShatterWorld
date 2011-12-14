<?php
namespace Rules\Facilities;
use Rules\AbstractRule;

class Mine extends AbstractRule implements IFacility
{
	public function getDescription ()
	{
		return 'Důl';
	}

	public function getDependencies ()
	{
		return array();
	}

	public function getConstructionCost ($level = 1)
	{
		return array(
			'stone' => $level * 40,
			'metal' => ($level - 1) * 20
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
			'metal' => $level / 100
		);
	}

	public function getDefenceBonus ()
	{
		return 0;
	}
}
