<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Meadow extends AbstractField implements IField
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
			'food' => 0.3,
			'fuel' => -0.9,
			'metal' => -0.4,
			'stone' => -0.4
		);
	}

	public function getDefenceBonus ()
	{
		return 0;
	}

}
