<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Entities;

class Colonisation extends AbstractRule implements IMove
{
	public function process ($id)
	{
		$move = $this->getContext()->model->getMoveRepository()->findOneByEvent($id);
		if ($move->target->owner === NULL) {
			$move->target->setOwner($move->clan);
		}
		$this->getContext()->model->getFieldService()->invalidateVisibleFields($move->clan->id);
	}
	
	public function getInfo ($eventId)
	{
		return $this->getContext()->model->getMoveRepository()->getEventInfo($eventId);
	}
	
	public function isValid (Entities\Field $origin, Entities\Field $target)
	{
		if ($target->owner === NULL) {
			$valid = FALSE;
			foreach ($this->getContext()->model->getFieldRepository()->getFieldNeighbours($target) as $neighbour) {
				if ($neighbour->owner == $origin->owner) {
					$valid = TRUE;
					break;
				}
			}
			return $valid;
		}
		return FALSE;
	}
}