<?php
namespace Services;
use Entities;

class Move extends Event
{
	public function startColonisation (Entities\Field $target, Entities\Clan $clan)
	{
		$this->create(array(
			'owner' => $clan,
			'target' => $target,
			'origin' => $clan->getHeadquarters(),
			'type' => 'colonisation',
			'timeout' => $this->context->stats->getColonisationTime($target, $clan)
		));
	}
	
	public function startUnitMovement (Entities\Field $origin, Entities\Field $target, $units)
	{
		$distance = $this->context->model->getFieldRepository()->calculateDistance($origin, $target);
		$speed = 0;
		$move = $this->create(array(
			'type' => 'unitMovement',
			'owner' => $origin->owner,
			'origin' => $origin,
			'target' => $target
		), FALSE);
		foreach ($units as $type => $count) {
			if ($unit = $this->context->model->getUnitRepository()->findOneBy(array('location' => $origin, 'type' => $type, 'move' => NULL))) {
				if ($unit->count > $count) {
					$unit->count = $unit->count - $count;
					$unit = $this->context->model->getUnitService()->create(array(
						'type' => $type,
						'owner' => $origin->owner,
						'location' => $origin,
						'count' => $count,
						'move' => $move
					), FALSE);
				} else {
					$unit->move = $move;
				}
				$this->entityManager->persist($unit);
				if (($unitSpeed = $this->context->rules->get('unit', $type)->getSpeed()) > $speed) {
					$speed = $unitSpeed;
				}
			}
		}
		$move->timeout = $speed * $distance;
		$this->entityManager->flush();
	}
}