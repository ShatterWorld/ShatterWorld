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
			'food' => -20,
			'metal' => 40,
			'stone' => 60
		);
	}

	public function getDefenceBonus ()
	{
		return 0.5;
	}

}
