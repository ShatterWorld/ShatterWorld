<?php
namespace Rules\Fields;
use Rules\AbstractRule;
use Nette;

class Plain extends AbstractRule implements IField
{
	public function getProbability ()
	{
		return 4;
	}
}