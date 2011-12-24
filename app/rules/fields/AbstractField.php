<?php
namespace Rules\Fields;
use Nette;
use Rules;

abstract class AbstractField extends Rules\AbstractRule implements IField
{
	public function getValue ()
	{
		return 350;
	}

}
