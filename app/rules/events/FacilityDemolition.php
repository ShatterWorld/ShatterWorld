<?php
namespace Rules\Events;
use Rules\AbstractRule;

class FacilityDemolition extends AbstractRule implements IConstruction
{
	public function process ($id)
	{
		$construction = $this->getContext()->model->getConstructionRepository()->findOneByEvent($id);
		$facility = $construction->level > 0 ? $construction->constructionType : NULL;
		$this->getContext()->model->getFieldService()->update($construction->field, array(
			'facility' => $facility,
			'level' => $construction->level
		));
		$this->getContext()->model->getResourceService()->recalculateProduction($construction->event->owner, $construction->event->term);
	}
	
	public function getInfo ($eventId)
	{
		return $this->getContext()->model->getConstructionRepository()->getEventInfo($eventId);
	}
	
	public function isValid ($level, Entities\Field $field = NULL)
	{
		$clan = $this->getContext()->model->getClanRepository()->getPlayerClan();
		return $field->owner == $clan && ($level === 0 || ($field->level - 1 === $level));
	}
}