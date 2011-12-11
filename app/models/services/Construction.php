<?php
namespace Services;
use Nette\Utils\Json;
use Entities;
use Rules\Facilities\IConstructionFacility;
use InsufficientResourcesException;
use InsufficientCapacityException;
use MultipleConstructionsException;
use MissingDependencyException;
use ConflictException;
use Nette\Diagnostics\Debugger;

class Construction extends Event
{
	/**
	 * Event constructor
	 * @param array of mixed
	 * @param boolean
	 * @return Entities\Construction
	 * @throws MultipleConstructionsException
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
		return parent::create($values, $flush);
	}

	/**
	 * Start colonisation of given field
	 * @param Entities\Field
	 * @param Entities\Clan
	 * @return void
	 */
	public function startColonisation (Entities\Field $target, Entities\Clan $clan)
	{
		$cost = $this->context->stats->colonisation->getCost($target, $clan);

		if ($this->context->model->getResourceRepository()->checkResources($clan, $cost)) {
			$this->create(array(
				'owner' => $clan,
				'target' => $target,
				'origin' => $clan->getHeadquarters(),
				'type' => 'colonisation',
				'timeout' => floor($this->context->stats->colonisation->getTime($target, $clan))
			), FALSE);
			$this->context->model->getResourceService()->pay($clan, $cost, NULL, FALSE);
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
		$clan = $field->owner;
		$stats = $this->context->stats->construction;
		$price = $stats->getConstructionCost($clan, $facility, $level);
		if ($this->context->model->getResourceRepository()->checkResources($clan, $price)) {
			$this->create(array(
				'target' => $field,
				'owner' => $field->owner,
				'type' => 'facilityConstruction',
				'construction' => $facility,
				'level' => $level,
				'timeout' => $stats->getConstructionTime($clan, $facility, $level)
			), FALSE);
			$this->context->model->getResourceService()->pay($clan, $price, NULL, FALSE);
			$this->context->model->getClanService()->issueOrder($clan, FALSE);
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
		$clan = $field->owner;
		$stats = $this->context->stats->construction;
		$price = $stats->getDemolitionCost($clan, $field->facility, $field->level, $level);
		if ($this->context->model->getResourceRepository()->checkResources($field->owner, $price)) {
			$this->create(array(
				'target' => $field,
				'owner' => $field->owner,
				'type' => 'facilityDemolition',
				'construction' => $field->facility,
				'level' => $level,
				'timeout' => $stats->getDemolitionTime($clan, $field->facility, $field->level, $level)
			), FALSE);
			$this->context->model->getResourceService()->pay($field->owner, $price, NULL, FALSE);
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
		$clan = $field->owner;
		$this->create(array(
			'target' => $field,
			'owner' => $field->owner,
			'type' => 'abandonment',
			'timeout' => $this->context->stats->construction->getAbandonmentTime($field->level)
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
			if ($this->context->stats->units->getUnitLevel($clan, $type) <= 0){
				throw new RuleViolationException;
			}
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
		$availableSlots = $this->context->stats->units->getSlots($clan);
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
		$this->context->model->getResourceService()->pay($clan, $price, NULL, FALSE);
		$this->context->model->getClanService()->issueOrder($clan, FALSE);
		$this->entityManager->flush();
	}

	/**
	 * Start a research
	 * @param Entities\Clan
	 * @param string
	 * @return void
	 */
	public function startResearch (Entities\Clan $clan, $type)
	{
		$level = 1;
		$research = $this->context->model->getResearchRepository()->getClanResearch($clan, $type);
		if ($research !== null){
			$level = $research->level + 1;
		}

		$stats = $this->context->stats->research;
		$rule = $this->context->rules->get('research', $type);
		$price = $stats->getCost($clan, $type, $level);
		if ($this->context->model->getResourceRepository()->checkResources($clan, $price)) {

			$researched = $this->context->model->getResearchRepository()->getResearched($clan);
			foreach ($rule->getDependencies() as $key => $dependency){
				if (!isset($researched[$key]) || $researched[$key]->level < $dependency){
					throw new \MissingDependencyException;
				}
			}

			foreach ($rule->getConflicts() as $key => $conflict){
				if (isset($researched[$key]) && $researched[$key]->level > $conflict){
					throw new \ConflictException;
				}
			}

			$running = $this->context->model->getConstructionRepository()->getRunningResearches($clan);
			if (isset($running[$type])){
				throw new MultipleConstructionsException;
			}

			$this->create(array(
				'target' => $clan->getHeadquarters(),
				'owner' => $clan,
				'type' => 'research',
				'construction' => $type,
				'level' => $level,
				'timeout' => $stats->getTime($clan, $type, $level)
			), FALSE);
			$this->context->model->getResourceService()->pay($clan, $price, NULL, FALSE);
			$this->context->model->getClanService()->issueOrder($clan, FALSE);
			$this->entityManager->flush();
		} else {
			throw new InsufficientResourcesException;
		}

	}

	/**
	 * Start exhaustion timer
	 * @param Entities\Clan
	 * @param array of int
	 * @param int
	 * @return void
	 */
	public function startResourceExhaustion (Entities\Clan $clan, $account)
	{
		//delete/update previous timers

		$time = floor($account->balance / -$account->production);
		$this->create(array(
			'owner' => $clan,
			'target' => $clan->getHeadquarters(),
			//'origin' => $clan->getHeadquarters(),
			'type' => 'resourceExhaustion',
			'construction' => $account->type,
			'timeout' => $time
		), FALSE);

		$this->entityManager->flush();
	}

}
