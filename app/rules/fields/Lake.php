<?php
namespace Rules\Fields;
use Nette;

class Lake extends Nette\Object implements IField
{
	public function getProbability ()
	{
		return 3;
	}
}