<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Lake extends AbstractField implements IField
{
	public function getDescription ()
	{
		return 'Jezero';
	}

	public function getProbability ()
	{
		return 3;
	}

	public function getOilProbability ()
	{
		return 0;
	}

	public function getProductionBonuses ()
	{
		return array(
			'food' => 0.45,
			'fuel' => -0.9,
			'stone' => -0.9,
			'metal' => -0.9
		);
	}

	public function getDefenceBonus ()
	{
		return -0.3;
	}

}
