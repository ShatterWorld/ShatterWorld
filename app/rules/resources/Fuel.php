<?php
namespace Rules\Resources;
use Rules\AbstractRule;

class Fuel extends AbstractRule implements IResource
{
	public function getInitialAmount ()
	{
		return 10;
	}
}