<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Forest extends AbstractField implements IField
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
			'food' => 0.2,
			'fuel' => 0.1,
			'metal' => -0.3,
			'stone' => -0.2
		);
	}

	public function getDefenceBonus ()
	{
		return 0;
	}
}
