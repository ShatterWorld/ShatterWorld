<?php
namespace GameModule;
use Nette;
use Nette\Diagnostics\Debugger;
use InsufficientResourcesException;
use RuleViolationException;

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
		$resources = $this->context->rules->getAll('resource');
		$zeroCost = array_combine(array_keys($resources), array_fill(0, count($resources), 0));
		foreach ($this->context->stats->getAvailableFacilities($this->getPlayerClan()) as $facility) {
			$rule = $this->context->rules->get('facility', $facility);
			$facilities[$facility] = array();
			$facilities[$facility]['cost'] = array_merge($zeroCost, $rule->getConstructionCost(1));
			$facilities[$facility]['time'] = $rule->getConstructionTime(1);
		}
		$this->payload->facilities = $facilities;
		$this->sendPayload();
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
			$this->context->model->getMoveService()->startColonisation($this->context->model->getFieldRepository()->find($targetId), $this->getPlayerClan());
			$this->flashMessage('Kolonizace zahájena');
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
		} catch (InsufficientResourcesException $e) {
			$this->flashMessage('Nemáte dostatek surovin', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze stavět na cizím, nebo zastaveném poli', 'error');
		}
	}
}
