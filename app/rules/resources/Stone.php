<?php
namespace Rules\Resources;
use Rules\AbstractRule;

class Stone extends AbstractRule implements IResource
{
	public function getInitialAmount ()
	{
		return 30;
	}
}