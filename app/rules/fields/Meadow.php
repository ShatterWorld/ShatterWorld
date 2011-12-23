<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Meadow extends AbstractRule implements IField
{
	public function getDescription ()
	{
		return 'Louka';
	}

	public function getProbability ()
	{
		return 5;
	}

	public function getOilProbability ()
	{
		return 0;
	}

	public function getProductionBonuses ()
	{
		return array(
			'food' => 30,
			'fuel' => -90,
			'metal' => -40,
			'stone' => -40
		);
	}

	public function getDefenceBonus ()
	{
		return 0;
	}

	public function getValue ()
	{
		return 350;
	}

}
