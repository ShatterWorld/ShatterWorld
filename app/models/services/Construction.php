<?php
namespace Services;
use Nette\Utils\Neon;
use Entities;
use Rules\Facilities\IConstructionFacility;
use InsufficientResourcesException;
use InsufficientCapacityException;

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
		$price = $rule->getConstructionCost($level);
		if ($this->context->model->getResourceRepository()->checkResources($field->owner, $price)) {
			$this->create(array(
				'target' => $field,
				'owner' => $field->owner,
				'type' => 'facilityConstruction',
				'construction' => $facility,
				'level' => $level,
				'timeout' => $rule->getConstructionTime($level)
			));
			$this->context->model->getResourceService()->pay($field->owner, $price);
		} else {
			throw new InsufficientResourcesException;
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
		$price = $rule->getDemolitionCost($field->level, $level);
		if ($this->context->model->getResourceRepository()->checkResources($field->owner, $price)) {
			$this->create(array(
				'target' => $field,
				'owner' => $field->owner,
				'type' => 'facilityDemolition',
				'construction' => $level > 0 ? 'downgrade' : 'demolition',
				'level' => $level,
				'timeout' => $rule->getDemolitionTime($field->level, $level)
			));
			$this->context->model->getResourceService()->pay($field->owner, $price);
		} else {
			throw new InsufficientResourcesException;
		}
	}
	
	/**
	 * Start abandonment of given field
	 * @param Entities\Field
	 * @return void
	 */
	public function startAbandonment (Entities\Field $field)
	{
		$this->create(array(
			'target' => $field,
			'owner' => $field->owner,
			'type' => 'abandonment',
			'timeout' => $this->context->stats->getAbandonmentTime($field->level)
		));
	}
	
	/** 
	 * Start training specified number of units
	 * @param Entities\Clan
	 * @param Entities\Field
	 * @param array of $type => $count
	 * @return void
	 */
	public function startUnitTraining (Entities\Clan $clan, $list)
	{
		$price = array();
		$difficulty = array();
		$timeout = 0;
		foreach ($list as $type => $count) {
			$rule = $this->context->rules->get('unit', $type);
			foreach ($rule->getCost() as $resource => $cost) {
				if (array_key_exists($resource, $price)) {
					$price[$resource] = $price[$resource] + ($cost * $count);
				} else {
					$price[$resource] = ($cost * $count);
				}
			}
			foreach ($rule->getDifficulty() as $slot => $amount) {
				if (array_key_exists($slot, $difficulty)) {
					$difficulty[$slot] = $difficulty[$slot] + ($amount * $count);
				} else {
					$difficulty[$slot] = ($amount * $count);
				}
			}
			if (($time = $rule->getTrainingTime()) > $timeout) {
				$timeout = $time;
			}
		}
		if (!$this->context->model->getResourceRepository()->checkResources($clan, $price)) {
			throw new InsufficientResourcesException;
		}
		$usedSlots = $this->context->model->getConstructionRepository()->getUsedUnitSlots($clan);
		$availableSlots = array();
		foreach ($this->context->model->getFieldRepository()->findByOwner($clan->id) as $clanField) {
			$facility = $clanField->facility;
			if ($facility !== NULL) {
				$rule = $this->context->rules->get('facility', $facility);
				if ($rule instanceof IConstructionFacility) {
					if (array_key_exists($facility, $availableSlots)) {
						$availableSlots[$facility] = $availableSlots[$facility] + $rule->getCapacity($clanField->level);
					} else {
						$availableSlots[$facility] = $rule->getCapacity($clanField->level);
					}
				}
			}
		}
		foreach ($difficulty as $slot => $amount) {
			if (!isset($availableSlots[$slot]) || ((isset($usedSlots[$slot]) ? $usedSlots[$slot] : 0) + $amount) > $availableSlots[$slot]) {
				throw new InsufficientCapacityException;
			}
		}
		$this->create(array(
			'target' => $clan->getHeadquarters(),
			'owner' => $clan,
			'type' => 'unitTraining',
			'construction' => Neon::encode($list, Neon::BLOCK),
			'timeout' => $timeout
		));
		$this->context->model->getResourceService()->pay($clan, $price);
	}
}