<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Moor extends AbstractField implements IField
{
	public function getDescription ()
	{
		return 'Močál';
	}

	public function getProbability ()
	{
		return 4;
	}

	public function getOilProbability ()
	{
		return 0;
	}

	public function getProductionBonuses ()
	{
		return array(
			'fuel' => 0.2,
			'stone' => -0.4,
			'food' => -0.3
		);
	}

	public function getDefenceBonus ()
	{
		return 0;
	}

}
