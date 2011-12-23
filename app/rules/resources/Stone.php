<?php
namespace Rules\Resources;
use Rules\AbstractRule;
use Entities;

class Stone extends AbstractRule implements IResource
{
	public function getDescription ()
	{
		return 'Kámen';
	}

	public function getValue (Entities\Resource $resource)
	{
		return $resource->production * 1800;
	}
}
