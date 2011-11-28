<?php
namespace Rules\Events;
use Rules\AbstractRule;
use ReportItem;
use Entities;

class Colonisation extends AbstractRule implements IConstruction
{
	public function getDescription ()
	{
		return 'Kolonizace';
	}

	public function process (Entities\Event $event, $processor)
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

	public function formatReport (Entities\Report $report)
	{
		return array(
			ReportItem::create('text', 'Úspěšně jsme připojili další území k našemu teritoriu!')
		);
	}
	
	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Kolonizace pole %s', $event->target->getCoords());
	}
	
	public function isExclusive ()
	{
		return TRUE;
	}
}
