<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Lake extends AbstractRule implements IField
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
			'food' => 40,
			'fuel' => -90,
			'stone' => -90,
			'metal' => -90
		);
	}
}