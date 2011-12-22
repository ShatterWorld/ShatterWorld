<?php
namespace Rules\Facilities;
use Rules\AbstractRule;

class MineField extends AbstractRule implements IFacility
{
	public function getDescription ()
	{
		return 'Minové pole';
	}

	public function getDependencies ()
	{
		return array(
			'mineField' => 1
		);
	}

	public function getConstructionCost ($level = 1)
	{
		return array(
			'stone' => $level * 1000,
			'metal' => $level * 1000
		);
	}

	public function getConstructionTime ($level = 1)
	{
		return pow($level, 2) * 200;
	}

	public function getDemolitionCost ($from, $level = 0)
	{
		return array(
			'stone' => $level * 1000 + 500,
			'metal' => $level * 1000 + 500,
			'fuel' => $level * 1000 + 500,
			'food' => $level * 1000 + 500
		);
	}

	public function getDemolitionTime ($from, $level = 0)
	{
		return ($from - $level) * 900;
	}

	public function getProduction ($level = 1)
	{
		return array();
	}

	public function getDefenceBonus ($level = 1)
	{
		return 0;
	}
}
