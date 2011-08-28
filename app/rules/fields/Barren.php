<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Barren extends AbstractRule implements IField
{
	public function getProbability ()
	{
		return 7;
	}
}