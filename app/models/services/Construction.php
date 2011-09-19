<?php
namespace Services;
use Entities;
use Exception;

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
	
	/**
	 * Start demolition on given field
	 * @param Entities\Field
	 * @param int
	 * @throws InsufficientResourcesException
	 * @return void
	 */
	public function startFacilityDemolition (Entities\Field $field, $level = 0)
	{
		$rule = $this->context->rules->get('facility', $field->facility);
		if ($this->context->rules->get('event', 'facilityDemolition')->isValid($field->facility, $level, $field)) {
			$this->context->model->getResourceService()->pay($field->owner, $rule->getDemolitionCost($field->level, $level));
			$this->create(array(
				'field' => $field,
				'type' => 'facilityDemolition',
				'constructionType' => $level > 0 ? 'downgrade' : 'demolition',
				'level' => $level,
				'timeout' => $rule->getDemolitionTime($field->level, $level)
			));
		} else {
			throw new Exception;
		}
	}
}