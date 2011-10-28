<?php
namespace Rules\Events;
use Entities;

class Occupation extends Attack
{
	public function getDescription ()
	{
		return 'Dobyvačný útok';
	}
	
	public function process (Entities\Event $event)
	{
		$result = parent::process($event);
		$model = $this->getContext()->model;
		if ($result['successful']) {
			foreach ($model->getConstructionRepository()->findBy(array('target' => $event->ŧarget->id)) as $construction) {
				$construction->failed = TRUE;
			}
			$event->target->owner = $event->owner;
		}
		return $result;
	}
	
	public function formatReport (Entities\Report $report)
	{
		return array();
	}
	
	public function needsTotalVictory ()
	{
		return TRUE;
	}
}