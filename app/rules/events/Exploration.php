<?php
namespace Rules\Events;
use Rules\AbstractRule;
use ReportItem;
use Entities;

class Exploration extends AbstractRule implements IConstruction
{
	public function getDescription ()
	{
		return 'Průzkum';
	}

	public function process (Entities\Event $event)
	{
		/*
		$event->target->setOwner($event->owner);
		$this->getContext()->model->getFieldService()->invalidateVisibleFields($event->owner->id);
		return array();*/

		//send resources !!!
	}

	public function isValid (Entities\Event $event)
	{
		if ($event->target->owner === NULL) {
			return true;
			/*
			$valid = FALSE;
			foreach ($this->getContext()->model->getFieldRepository()->getFieldNeighbours($event->target) as $neighbour) {
				if ($neighbour->owner == $event->owner) {
					$valid = TRUE;
					break;
				}
			}
			return $valid;
		}*/
		}
		return FALSE;
	}

	public function formatReport (Entities\Report $report)
	{
		return array(
			new ReportItem('text', 'Prozkoumáno!')
		);
	}

	public function isExclusive ()
	{
		return false;
	}
}
