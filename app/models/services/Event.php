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
	
	public function create ($values, $flush = TRUE)
	{
		$values['term'] = new \DateTime('@' . (date('U') + $values['term']));
		return parent::create($values, $flush);
	}
}