<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Entities;

class FacilityConstruction extends AbstractRule implements IEvent
{
	public function process (Entities\Event $event)
	{
		$this->getContext()->model->getFieldService()->update($event->target, array(
			'facility' => $event->construction,
			'level' => $event->level
		));
		$this->getContext()->model->getResourceService()->recalculateProduction($event->owner, $event->term);
	}
	
	public function isValid (Entities\Event $event)
	{
		$clan = $this->getContext()->model->getClanRepository()->getPlayerClan();
		return $event->target->owner == $clan && $event->level <= $this->getContext()->params['game']['stats']['facilityLevelCap'] &&
			(($event->target->facility === $event->construction || ($event->target->facility === NULL && $event->level === 1)) && $event->target->level === ($event->level - 1));
	}
}
