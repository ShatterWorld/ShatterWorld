<?php
namespace Rules\Fields;
use Nette;

class Meadow extends Nette\Object implements IField
{
	public function getProbability ()
	{
		return 5;
	}
}