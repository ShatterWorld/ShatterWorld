<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Entities;

class FacilityConstruction extends AbstractRule implements IConstruction
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
	
	public function isValid ($type, $level, Entities\Field $field = NULL)
	{
		$clan = $this->getContext()->model->getClanRepository()->getPlayerClan();
		return $field->owner == $clan && 
			(($field->facility === $type || ($field->facility === NULL && $level === 1)) && $field->level === ($level - 1));
	}
}
