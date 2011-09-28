<?php
namespace Services;
use Entities;

class Unit extends BaseService
{
	public function addUnits (Entities\Field $field, $list)
	{
		foreach ($list as $type => $count) {
			$this->create(array(
				'type' => $type,
				'owner' => $field->owner,
				'count' => $count,
				'location' => $field
			), FALSE);
		}
		$this->entityManager->flush();
	}
}