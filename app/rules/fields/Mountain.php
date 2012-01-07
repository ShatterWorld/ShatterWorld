<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Mountain extends AbstractField implements IField
{
	public function getDescription ()
	{
		return 'Hora';
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
			'food' => -0.2,
			'metal' => 0.4,
			'stone' => 0.6
		);
	}

	public function getDefenceBonus ()
	{
		return 0.5;
	}

}
