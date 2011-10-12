<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Entities;

class Colonisation extends AbstractRule implements IConstruction
{
	public function getDescription ()
	{
		return 'Kolonizace';
	}

	public function process (Entities\Event $event)
	{
		$event->target->setOwner($event->owner);
		$this->getContext()->model->getFieldService()->invalidateVisibleFields($event->owner->id);
		return array();
	}
	
	public function isValid (Entities\Event $event)
	{
		if ($event->target->owner === NULL) {
			$valid = FALSE;
			foreach ($this->getContext()->model->getFieldRepository()->getFieldNeighbours($event->target) as $neighbour) {
				if ($neighbour->owner == $event->owner) {
					$valid = TRUE;
					break;
				}
			}
			return $valid;
		}
		return FALSE;
	}
	
	public function formatReport ($data)
	{
		return $data;
	}
	
	public function isExclusive ()
	{
		return TRUE;
	}
}