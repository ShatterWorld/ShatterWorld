<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Moor extends AbstractRule implements IField
{
	public function getDescription ()
	{
		return 'Močál';
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
			'fuel' => 20,
			'stone' => -40,
			'metal' => -40
		);
	}
}