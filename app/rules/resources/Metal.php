<?php
namespace Rules\Resources;
use Rules\AbstractRule;

class Metal extends AbstractRule implements IResource
{
	public function getDescription ()
	{
		return 'Kov';
	}

	public function getValue ()
	{
		return 0.02;
	}
}
