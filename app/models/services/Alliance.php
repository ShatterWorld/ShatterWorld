<?php
namespace Services;

class Alliance extends BaseService
{
	public function delete ($object, $flush = TRUE)
	{
		foreach ($object->getMembers() as $member) {
			$member->setAlliance(NULL);
		}
		parent::delete($object, $flush);
	}
}