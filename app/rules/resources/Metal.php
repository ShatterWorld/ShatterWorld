<?php
namespace Rules\Resources;
use Rules\AbstractRule;

class Metal extends AbstractRule implements IResource
{
	public function getInitialAmount ()
	{
		return 10;
	}
}