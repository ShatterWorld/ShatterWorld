<?php
namespace Services;

class Facility extends BaseService
{
	public function delete ($object, $flush = TRUE)
	{
		$object->location->setFacility(NULL);
		parent::delete($object, $flush);
	}
}