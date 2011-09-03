<?php
namespace Rules\Resources;
use Rules\AbstractRule;

class Food extends AbstractRule implements IResource
{
	public function getInitialAmount ()
	{
		return 50;
	}
}