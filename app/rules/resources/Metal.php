<?php
namespace Rules\Resources;
use Rules\AbstractRule;
use Entities;

class Metal extends AbstractRule implements IResource
{
	public function getDescription ()
	{
		return 'Kov';
	}

	public function getValue (Entities\Resource $resource)
	{
		return $resource->production * 1800;
	}
}
