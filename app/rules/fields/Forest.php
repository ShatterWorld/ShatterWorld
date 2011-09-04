<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Forest extends AbstractRule implements IField
{
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
		return array();
	}
}