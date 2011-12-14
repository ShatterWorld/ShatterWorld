<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Forest extends AbstractRule implements IField
{
	public function getDescription ()
	{
		return 'Les';
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
			'food' => 20,
			'fuel' => 10,
			'metal' => -30,
			'stone' => -20
		);
	}

	public function getDefenceBonus ()
	{
		return 0;
	}
}
