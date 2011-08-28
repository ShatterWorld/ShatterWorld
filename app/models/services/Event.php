<?php
namespace Services;

class Event extends BaseService
{
	public function processPendingEvents ()
	{
		foreach ($this->getRepository()->findPendingEvents() as $event) {
			$eventRule = $this->context->rules->get('event', $event->type);
			$eventRule->process($event->id);
			$this->update($event, array('processed' => TRUE), FALSE);
		}
		$this->entityManager->flush();
	}
}