<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Lake extends AbstractRule implements IField
{
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
		return array();
	}
}