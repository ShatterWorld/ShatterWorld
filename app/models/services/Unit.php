<?php
namespace Services;
use Entities;
use Nette\Diagnostics\Debugger;

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

	public function setupExhaustionTimer ($clan, $loss)
	{
		$resources = $this->context->model->getResourceRepository()->getResourcesArray($clan);
		//delete previous ex. timers

		//cout new ones
		$units = $this->getRepository()->getClanUnits($clan);

		//sort units
		$rules = $this->context->rules;
		usort($units, function ($a, $b) use ($rules){
			$aUpkeep = $rules->get('units', $a->type)->getUpkeep();
			$bUpkeep = $rules->get('units', $b->type)->getUpkeep();
			if ($aUpkeep == $bUpkeep) return 0;
			return ($aUpkeep < $bUpkeep) ? -1 : 1;
		});


		//determine
		$production = -$resources['food']['production'];
		$time = floor($resources['food']['balance'] / $production);

		foreach($units as $unit){
			if ($production <= 0){
				break;
			}

			$upkeep = $rules->get('units', $unit->type)->getUpkeep();
			$count = min($units->count, floor($production / $upkeep));

			//set timer
			$production -= $upkeep * $count;

		}


	}
}













