<?php
namespace Rules\Events;
use Entities;
use Rules\AbstractRule;

class FacilityDemolition extends AbstractRule implements IEvent
{
	public function process (Entities\Event $event)
	{
		$changes = array('level' => $event->level);
		if ($event->level === 0) {
			$changes['facility'] = NULL;
		}
		$this->getContext()->model->getFieldService()->update($event->target, $changes);
		$this->getContext()->model->getResourceService()->recalculateProduction($event->owner, $event->term);
	}
	
	public function isValid (Entities\Event $event)
	{
		$clan = $this->getContext()->model->getClanRepository()->getPlayerClan();
		return $event->target->owner == $clan && ($event->level === 0 || ($event->target->level - 1 === $event->level));
	}
}