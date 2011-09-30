<?php
namespace Services;
use Entities;

class Unit extends BaseService
{
	public function addUnits (Entities\Field $field, $list)
	{
		foreach ($list as $type => $count) {
			if ($unit = $this->getRepository()->findOneBy(array('location' => $field, 'type' => $type, 'move' => NULL))) {
				$unit->count = $unit->count + $count;
				$this->entityManager->persist($unit);
			} else {
				$this->create(array(
					'type' => $type,
					'owner' => $field->owner,
					'count' => $count,
					'location' => $field
				), FALSE);
			}
		}
		$this->entityManager->flush();
	}
}