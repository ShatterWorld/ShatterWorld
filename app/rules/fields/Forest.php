<?php
namespace Rules\Fields;
use Nette;

class Forest extends Nette\Object implements IField
{
	public function getProbability ()
	{
		return 5;
	}
}