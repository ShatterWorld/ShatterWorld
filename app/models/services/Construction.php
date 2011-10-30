<?php
namespace Services;
use Nette\Utils\Json;
use Entities;
use Rules\Facilities\IConstructionFacility;
use InsufficientResourcesException;
use InsufficientCapacityException;
use MultipleConstructionsException;

class Construction extends Event
{
	/**
	 * Event constructor
	 * @param array of mixed
	 * @param boolean
	 * @return Entities\Construction
	 */
	public function create ($values, $flush = TRUE)
	{
		if ($this->context->rules->get('event', $values['type'])->isExclusive()) {
			foreach ($this->getRepository()->findPendingConstructions($values['owner'], $values['target']) as $construction) {
				if ($this->context->rules->get('event', $construction->type)->isExclusive()) {
					throw new MultipleConstructionsException;
				}
			}
		}
		parent::create($values, $flush);
	}

	/**
	 * Start colonisation of given field
	 * @param Entities\Field
	 * @param Entities\Clan
	 * @return void
	 */
	public function startColonisation (Entities\Field $target, Entities\Clan $clan)
	{
		$cost = $this->context->stats->getColonisationCost($target, $clan);
		if ($this->context->model->getResourceRepository()->checkResources($clan, $cost)) {
			$this->create(array(
				'owner' => $clan,
				'target' => $target,
				'origin' => $clan->getHeadquarters(),
				'type' => 'colonisation',
				'timeout' => $this->context->stats->getColonisationTime($target, $clan)
			), FALSE);
			$this->context->model->getResourceService()->pay($clan, $cost, FALSE);
			$this->context->model->getClanService()->issueOrder($clan, FALSE);
			$this->entityManager->flush();
		} else {
			throw new \InsufficientResourcesException;
		}
	}

	/**
	 * Start exploration of given field
	 * @param Entities\Field
	 * @param Entities\Clan
	 * @return void
	 */
	public function startExploration (Entities\Field $target, Entities\Clan $clan)
	{
		$cost = $this->context->stats->getColonisationCost($target, $clan);
		if ($this->context->model->getResourceRepository()->checkResources($clan, $cost)) {
			$this->create(array(
				'owner' => $clan,
				'target' => $target,
				'origin' => $clan->getHeadquarters(),
				'type' => 'exploration',
				'timeout' => $this->context->stats->getColonisationTime($target, $clan)
			), FALSE);
			$this->context->model->getResourceService()->pay($clan, $cost, FALSE);
			$this->context->model->getClanService()->issueOrder($clan, FALSE);
			$this->entityManager->flush();
		} else {
			throw new \InsufficientResourcesException;
		}
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
		$price = $rule->getConstructionCost($level);
		if ($this->context->model->getResourceRepository()->checkResources($field->owner, $price)) {
			$this->create(array(
				'target' => $field,
				'owner' => $field->owner,
				'type' => 'facilityConstruction',
				'construction' => $facility,
				'level' => $level,
				'timeout' => $rule->getConstructionTime($level)
			), FALSE);
			$this->context->model->getResourceService()->pay($field->owner, $price, FALSE);
			$this->context->model->getClanService()->issueOrder($field->owner, FALSE);
			$this->entityManager->flush();
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
			), FALSE);
			$this->context->model->getResourceService()->pay($field->owner, $price, FALSE);
			$this->context->model->getClanService()->issueOrder($field->owner, FALSE);
			$this->entityManager->flush();
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
		), FALSE);
		$this->context->model->getClanService()->issueOrder($field->owner, FALSE);
		$this->entityManager->flush();
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
		if (!is_array($list)) {
			$list = (array) $list;
		}
		$list = array_filter($list);
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
		$availableSlots = $this->context->stats->getTotalUnitSlots($clan);
		foreach ($difficulty as $slot => $amount) {
			if (!isset($availableSlots[$slot]) || $availableSlots[$slot] < $amount) {
				throw new InsufficientCapacityException;
			}
		}
		$this->create(array(
			'target' => $clan->getHeadquarters(),
			'owner' => $clan,
			'type' => 'unitTraining',
			'construction' => Json::encode($list),
			'timeout' => $timeout
		), FALSE);
		$this->context->model->getResourceService()->pay($clan, $price, FALSE);
		$this->context->model->getClanService()->issueOrder($field->owner, FALSE);
		$this->entityManager->flush();
	}
}
