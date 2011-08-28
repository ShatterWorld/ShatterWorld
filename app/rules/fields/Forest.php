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
}