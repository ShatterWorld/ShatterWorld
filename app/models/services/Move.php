<?php
namespace Services;
use Entities;

class Move extends Event
{
	public function startUnitMovement (Entities\Field $origin, Entities\Field $target, Entities\Clan $owner, $type, $units, $cargo = array(), $term = NULL)
	{
		$time = 0;
		$move = $this->create(array(
			'type' => $type,
			'owner' => $owner,
			'origin' => $origin,
			'target' => $target,
			'cargo' => $cargo
		), FALSE);
		foreach ($units as $id => $count) {
			if ($unit = $this->context->model->getUnitRepository()->find($id)) {
				if ($unit->count > $count) {
					$unit->count = $unit->count - $count;
					$unit = $this->context->model->getUnitService()->create(array(
						'type' => $unit->type,
						'owner' => $unit->owner,
						'location' => $unit->location,
						'count' => $count,
						'move' => $move
					), FALSE);
				} else {
					$unit->move = $move;
				}
				$distance = $this->context->model->getFieldRepository()->calculateDistance($unit->location, $target);
				$speed = $this->context->rules->get('unit', $unit->type)->getSpeed();
				if ($distance * $speed > $time) {
					$time = $distance * $speed;
				}
			}
		}
		$move->setTimeout($time, $term);
		$this->entityManager->flush();
	}
}