<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Plain extends AbstractRule implements IField
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
			'food' => 20,
			'fuel' => -10,
			'metal' => -20,
			'stone' => -10
		);
	}
}