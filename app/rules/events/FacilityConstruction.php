<?php
namespace Rules\Events;
use Rules\AbstractRule;

class FacilityConstruction extends AbstractRule implements IEvent
{
	public function process ($id)
	{
		$construction = $this->getContext()->model->getConstructionRepository()->findOneByEvent($id);
		$this->getContext()->model->getFieldService()->update($construction->field, array(
			'facility' => $construction->constructionType,
			'level' => 1
		));
		$this->getContext()->model->getResourceService()->recalculateProduction($construction->event->owner, $construction->event->term);
	}
	
	public function getInfo ($eventId)
	{
		return $this->getContext()->model->getConstructionRepository()->getEventInfo($eventId);
	}
}
