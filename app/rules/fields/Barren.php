<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Barren extends AbstractField implements IField
{
	public function getDescription ()
	{
		return 'Pustina';
	}

	public function getProbability ()
	{
		return 7;
	}

	public function getOilProbability ()
	{
		return 0;
	}

	public function getProductionBonuses ()
	{
		return array(
			'food' => -0.3,
			'fuel' => -0.4,
			'metal' => -0.4
		);
	}

	public function getDefenceBonus ()
	{
		return 0.2;
	}

}
