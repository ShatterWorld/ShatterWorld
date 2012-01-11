<?php
namespace Rules\Facilities;
use Rules\AbstractRule;

class Bunker extends AbstractRule implements IFacility
{
	public function getDescription ()
	{
		return 'Bunkr';
	}

	public function getDependencies ()
	{
		return array(
			'bunker' => 1
		);
	}

	public function getConstructionCost ($level = 1)
	{
		return array(
			'food' => $level * 100,
			'stone' => $level * 500,
			'metal' => $level * 500
		);
	}

	public function getConstructionTime ($level = 1)
	{
		return 120 * 60 + pow($level, 3) * 7 * 60;
	}

	public function getDemolitionCost ($from, $level = 0)
	{
		return array();
	}

	public function getDemolitionTime ($from, $level = 0)
	{
		return ($from - $level) * 900;
	}

	public function getProduction ($level = 1)
	{
		return array(
			'food' => (-4 -log($level, 2)) / 3600,
		);
	}

	public function getDefenceBonus ($level = 1)
	{
		return $level * 0.5;
	}

	public function getValue ($level = 1)
	{
		return 100 + $level * 50;
	}
}
