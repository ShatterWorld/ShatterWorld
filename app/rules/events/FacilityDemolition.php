<?php
namespace Rules\Events;
use Entities;
use Rules\AbstractRule;

class FacilityDemolition extends AbstractRule implements IEvent
{
	public function process (Entities\Event $event)
	{
		$facility = $event->level > 0 ? $event->construction : NULL;
		$this->getContext()->model->getFieldService()->update($event->target, array(
			'facility' => $facility,
			'level' => $event->level
		));
		$this->getContext()->model->getResourceService()->recalculateProduction($event->owner, $event->term);
	}
	
	public function isValid (Entities\Event $event)
	{
		$clan = $this->getContext()->model->getClanRepository()->getPlayerClan();
		return $event->target->owner == $clan && ($event->level === 0 || ($event->target->level - 1 === $event->level));
	}
}