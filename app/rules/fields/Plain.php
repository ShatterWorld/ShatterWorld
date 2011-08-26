<?php
namespace Rules\Fields;
use Nette;

class Plain extends Nette\Object implements IField
{
	public function getProbability ()
	{
		return 4;
	}
}