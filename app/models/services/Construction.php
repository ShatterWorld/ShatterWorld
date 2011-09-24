<?php
namespace Services;
use Entities;
use Exception;

class Construction extends Event
{
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
		$this->create(array(
			'target' => $field,
			'owner' => $field->owner,
			'type' => 'facilityConstruction',
			'construction' => $facility,
			'level' => $level,
			'timeout' => $rule->getConstructionTime($level)
		));
		$this->context->model->getResourceService()->pay($field->owner, $rule->getConstructionCost($level));
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
		$this->create(array(
			'target' => $field,
			'owner' => $field->owner,
			'type' => 'facilityDemolition',
			'construction' => $level > 0 ? 'downgrade' : 'demolition',
			'level' => $level,
			'timeout' => $rule->getDemolitionTime($field->level, $level)
		));
		$this->context->model->getResourceService()->pay($field->owner, $rule->getDemolitionCost($field->level, $level));
	}
}