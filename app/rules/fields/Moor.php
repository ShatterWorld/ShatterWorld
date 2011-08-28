<?php
namespace Rules\Fields;
use Nette;

class Moor extends Nette\Object implements IField
{
	public function getProbability ()
	{
		return 4;
	}
}