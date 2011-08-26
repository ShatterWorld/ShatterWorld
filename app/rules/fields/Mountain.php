<?php
namespace Rules\Fields;
use Nette;

class Mountain extends Nette\Object implements IField 
{
	public function getProbability ()
	{
		return 3;
	}
}