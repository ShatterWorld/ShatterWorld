<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Mountain extends AbstractRule implements IField 
{
	public function getProbability ()
	{
		return 3;
	}
}