<?php
namespace Services;
use Entities;
use Rules;
use Nette\Diagnostics\Debugger;

class Move extends Event
{
	protected function startMovement (Entities\Field $origin, Entities\Field $target, Entities\Clan $owner, $type, $units, $cargo = array(), $now = NULL)
	{
		$rule = $this->context->rules->get('event', $type);
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
				$map = $this->context->map;
				$distance = $map->calculateDistance($map->coords($unit->location), $map->coords($target));
				$speed = $this->context->rules->get('unit', $unit->type)->getSpeed();
				if ($distance * $speed > $time) {
					$time = $distance * $speed;
				}
			}
		}
		$move->setTimeout($time, $now);
		if ($rule instanceof Rules\Events\Attack) {
			$notificationTimeout = $time - min($time, floor(($time * $this->context->stats->visibility->getRadius($move->target->owner) / $distance)));
			$notification = $this->context->model->notificationService->create(array(
				'type' => 'attackNotification',
				'owner' => $move->target->owner,
				'term' => $move->term,
				'subject' => $move
			), FALSE);
			$notification->setActivationTimeout($notificationTimeout, $now);
		}
		return $move;
	}

	public function startUnitMovement (Entities\Field $origin, Entities\Field $target, Entities\Clan $owner, $type, $units, $cargo = array(), $now = NULL, $flush = TRUE)
	{
		$move = $this->startMovement($origin, $target, $owner, $type, $units, $cargo, $now);
		$this->context->model->getClanService()->issueOrder($owner, FALSE);
		if ($flush) {
			$this->entityManager->flush();
		}
		return $move;
	}

	public function startUnitReturn (Entities\Field $origin, Entities\Field $target, Entities\Clan $owner, $units, $cargo = array(), $now = NULL, $flush = TRUE)
	{
		$move = $this->startMovement($origin, $target, $owner, 'unitReturn', $units, $cargo, $now);
		if ($flush) {
			$this->entityManager->flush();
		}
		return $move;
	}
}
