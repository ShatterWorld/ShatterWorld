<?php
namespace Services;

class Construction extends BaseService
{
	public function create ($values, $flush = TRUE)
	{
		$values['event'] = $this->context->model->getEventService()->create(array(
			'term' => $values['timeout'],
			'type' => $values['type'],
			'owner' => $values['field']->owner
		));
		parent::create($values, $flush);
	}
}