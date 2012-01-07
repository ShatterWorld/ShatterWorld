<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Plain extends AbstractField implements IField
{
	public function getDescription ()
	{
		return 'Pláň';
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
			'food' => 0.2,
			'fuel' => -0.1,
			'metal' => -0.2,
			'stone' => -0.1
		);
	}

	public function getDefenceBonus ()
	{
		return 0;
	}

}
