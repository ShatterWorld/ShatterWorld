<?php
namespace Rules\Fields;
use Nette;

class Barren extends Nette\Object implements IField
{
	public function getProbability ()
	{
		return 7;
	}
}