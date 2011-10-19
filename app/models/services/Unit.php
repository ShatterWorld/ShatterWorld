<?php
namespace Services;
use Entities;

class Unit extends BaseService
{
	public function addUnits (Entities\Field $field, $list)
	{
		foreach ($list as $type => $count) {
			if ($unit = $this->getRepository()->findOneBy(array('location' => $field->id, 'type' => $type, 'move' => NULL))) {
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
		$this->context->model->getResourceService()->recalculateProduction($field->owner, new \DateTime);
	}
	
	public function removeUnits ($list)
	{
		foreach ($list as $id => $count) {
			if ($unit = $this->getRepository()->find($id)) {
				$newCount = $unit->count - $count;
				if ($newCount > 0) {
					$this->update($unit, array('count' => $newCount), FALSE);
				} else {
					$this->delete($unit);
				}
			}
		}
		$this->entityManager->flush();
	}
	
	public function moveUnits (Entities\Field $target, $list)
	{
		foreach ($list as $unit) {
			if ($base = $this->getRepository()->findOneBy(array('location' => $target->id, 'type' => $unit->type, 'move' => NULL))) {
				$base->count = $base->count + $unit->count;
				$this->remove($unit, FALSE);
			} else {
				$unit->move = NULL;
				$unit->location = $target;
			}
		}
		$this->entityManager->flush();
	}
}