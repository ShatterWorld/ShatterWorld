<?php
namespace GameModule;
use Nette;
use Nette\Diagnostics\Debugger;
use InsufficientResourcesException;
use RuleViolationException;
use MultipleConstructionsException;

class MapPresenter extends BasePresenter
{
	public function renderDefault ()
	{
		$this->template->clan = $this->getPlayerClan();
	}

	/**
	 * Send the map via ajax
	 * @return void
	 */
	public function handleFetchMap ()
	{
		$clan = $this->getPlayerClan();
		$this->payload->fields = $this->getFieldRepository()->getVisibleFieldsArray($clan, $this->context->stats->getVisibilityRadius($clan));
		$this->payload->clanId = $this->getPlayerClan()->id;
		$this->payload->allianceId = ($this->getPlayerClan()->alliance != null) ? $this->getPlayerClan()->alliance->id : null;
		$this->sendPayload();
	}

	public function handleFetchFacilities ()
	{
		$facilities = array();
		foreach ($this->context->stats->getAvailableFacilities($this->getPlayerClan()) as $facility) {
			$rule = $this->context->rules->get('facility', $facility);
			$facilities[$facility] = array();
			$facilities[$facility]['cost'] = $rule->getConstructionCost(1);
			$facilities[$facility]['time'] = $rule->getConstructionTime(1);
		}
		$this->payload->facilities = $facilities;
		$changes = $this->getFieldRepository()->getAvailableFacilityChanges($this->getPlayerClan());
		$this->payload->upgrades = $changes['upgrades'];
		$this->payload->downgrades = $changes['downgrades'];
		$this->payload->demolitions = $changes['demolitions'];
		$this->sendPayload();
	}

	public function handleAttack ($originId, $targetId)
	{
		$args = $this->request->params;
		$units = array();
		foreach ($args as $key => $arg) {
			if (is_numeric($key)) {
				$units[$key] = $arg;
			}
		}
		$origin = $this->getFieldRepository()->find($originId);
		$target = $this->getFieldRepository()->find($targetId);
		try {
			$this->getMoveService()->startUnitMovement($origin, $target, $this->getPlayerClan(), 'pillaging', $units);
			$this->flashMessage('Útok zahájen');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Takový útok není možný', 'error');
		}
	}

	public function handleFetchColonisationCost ($targetId)
	{
		$target = $this->getFieldRepository()->find($targetId);
		$clan = $this->getPlayerClan();
		$this->payload->cost = $this->context->stats->getColonisationCost($target, $clan);
		$this->payload->time = $this->context->stats->getColonisationTime($target, $clan);
		$this->sendPayload();
	}

	public function handleSendColonisation ($targetId)
	{
		try {
			$this->context->model->getConstructionService()->startColonisation($this->context->model->getFieldRepository()->find($targetId), $this->getPlayerClan());
			$this->flashMessage('Kolonizace zahájena');
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Toto pole už kolonizujete.', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze kolonizovat pole, se kterým nesousedíte', 'error');
		} catch (InsufficientResourcesException $e) {
			$this->flashMessage('Nemáte dostatek surovin', 'error');
		}
	}

	public function handleLeaveField ($targetId)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		try {
			$this->context->model->getConstructionService()->startAbandonment($target);
			$this->flashMessage('Opuštení zahájeno');
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Nelze opustit pole, na kterém právě probíhá stavba', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze opustit pole, které vám nepatří.', 'error');
		}
	}

	public function handleBuildFacility ($targetId, $facility)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		try {
			$this->context->model->getConstructionService()->startFacilityConstruction($target, $facility);
			$this->flashMessage('Stavba zahájena');
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Na tomto poli už probíhá nějaká stavba', 'error');
		} catch (InsufficientResourcesException $e) {
			$this->flashMessage('Nemáte dostatek surovin', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze stavět na cizím, nebo zastaveném poli', 'error');
		}
	}

	public function handleDestroyFacility ($targetId)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		try {
			$this->context->model->getConstructionService()->startFacilityDemolition($target, 0);
			$this->flashMessage('Demolice zahájena');
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Na tomto poli už probíhá nějaká stavba', 'error');
		} catch (InsufficientResourcesException $e) {
			$this->flashMessage('Nemáte dostatek surovin', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze bourat na cizím, nebo nezastaveném poli', 'error');
		}
	}

	public function handleUpgradeFacility ($targetId)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		try {
			$this->flashMessage('Stavba zahájena');
			$this->context->model->getConstructionService()->startFacilityConstruction($target, $target->facility, $target->level + 1);
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Na tomto poli už probíhá nějaká stavba', 'error');
		} catch (InsufficientResourcesException $e) {
			$this->flashMessage('Nemáte dostatek surovin', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze stavět na cizím, nebo zastaveném poli', 'error');
		}
	}

	public function handleDowngradeFacility ($targetId)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		try {
			$this->context->model->getConstructionService()->startFacilityDemolition($target, $target->level - 1);
			$this->flashMessage('Demolice zahájena');
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Na tomto poli už probíhá nějaká stavba', 'error');
		} catch (InsufficientResourcesException $e) {
			$this->flashMessage('Nemáte dostatek surovin', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze stavět na cizím, nebo zastaveném poli', 'error');
		}
	}

	public function handleFetchExplorationCost ($targetId)
	{
		$target = $this->getFieldRepository()->find($targetId);
		$clan = $this->getPlayerClan();
		$this->payload->cost = $this->context->stats->getExplorationCost($target, $clan);
		$this->payload->time = $this->context->stats->getExplorationTime($target, $clan);
		$this->sendPayload();
	}

	public function handleSendExploration ($targetId)
	{
		try {
			$this->context->model->getConstructionService()->startExploration($this->context->model->getFieldRepository()->find($targetId), $this->getPlayerClan());
			$this->flashMessage('Průzkum zahájen');
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Toto pole už kolonizujete nebo ho již prozkoumáváte.', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze kolonizovat pole, se kterým nesousedíte', 'error');
		} catch (InsufficientResourcesException $e) {
			$this->flashMessage('Nemáte dostatek surovin', 'error');
		}
	}


}
