<?php
namespace Services;

class Move extends BaseService
{
	public function create ($values, $flush = TRUE)
	{
		$values['event'] = $this->context->model->getEventService()->create(array(
			'type' => $values['type'],
			'owner' => $values['clan'],
			'term' => $this->context->stats->getColonisationTime($values['origin'], $values['target'])
		));
		parent::create($values, $flush);
	}
}