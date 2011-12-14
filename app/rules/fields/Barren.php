<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Barren extends AbstractRule implements IField
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
			'food' => -30,
			'fuel' => -40,
			'metal' => -40
		);
	}

	public function getDefenceBonus ()
	{
		return 0.2;
	}
}
