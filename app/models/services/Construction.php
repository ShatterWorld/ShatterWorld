<?php
namespace Services;
use Entities;

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
	
	/**
	 * Start building given facility on given field
	 * @param Entities\Field
	 * @param string
	 * @param int
	 * @throws InsufficientResourcesException
	 * @return void
	 */
	public function startFacilityConstruction (Entities\Field $field, $facility, $level = 1)
	{
		$rule = $this->context->rules->get('facility', $facility);
		if ($this->context->rules->get('event', 'facilityConstruction')->isValid($facility, $level, $field)) {
			$this->context->model->getResourceService()->pay($field->owner, $rule->getConstructionCost($level));
			$this->create(array(
				'field' => $field,
				'type' => 'facilityConstruction',
				'constructionType' => $facility,
				'level' => $level,
				'timeout' => $rule->getConstructionTime($level)
			));
		} else {
			throw new Exception;
		}
	}
}