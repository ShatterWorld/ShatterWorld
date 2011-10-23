<?php
namespace Services;
use Entities;

class Unit extends BaseService
{
	public function addUnits (Entities\Field $field, Entities\Clan $owner, $list, $term = NULL)
	{
		foreach ($list as $type => $count) {
			if ($unit = $this->getRepository()->findUnit($owner, $field, $type)) {
				$unit->count = $unit->count + $count;
			} else {
				$this->create(array(
					'type' => $type,
					'owner' => $owner,
					'count' => $count,
					'location' => $field
				), FALSE);
			}
		}
		$this->entityManager->flush();
		$this->context->model->getResourceService()->recalculateProduction($owner, $term);
	}
	
	public function removeUnits ($owner, Entities\Field $location, $list, $term = NULL)
	{
		foreach ($list as $type => $count) {
			if ($unit = $this->getRepository()->findUnit($owner, $location, $type)) {
				$newCount = $unit->count - $count;
				if ($newCount > 0) {
					$this->update($unit, array('count' => $newCount), FALSE);
				} else {
					$this->delete($unit);
				}
			}
		}
		$this->entityManager->flush();
		$this->context->model->getResourceService()->recalculateProduction($owner, $term);
	}
	
	public function moveUnits (Entities\Field $target, Entities\Clan $owner, $list)
	{
		foreach ($list as $unit) {
			if ($base = $this->getRepository()->findUnit($owner, $target, $unit->type)) {
				$base->count = $base->count + $unit->count;
				$this->delete($unit, FALSE);
			} else {
				$unit->move = NULL;
				$unit->location = $target;
			}
		}
		$this->entityManager->flush();
	}
	
	public function delete ($object, $flush = TRUE)
	{
		$object->move = NULL;
		$object->location = NULL;
		parent::delete($object, $flush);
	}
}