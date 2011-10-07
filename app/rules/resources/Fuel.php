<?php
namespace Rules\Resources;
use Rules\AbstractRule;

class Fuel extends AbstractRule implements IResource
{
	public function getDescription ()
	{
		return 'Palivo';
	}

	public function getInitialAmount ()
	{
		return 100;
	}
}