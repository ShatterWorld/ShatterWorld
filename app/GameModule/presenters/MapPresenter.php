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
		$this->payload->fields = $this->getFieldRepository()->getVisibleFieldsArray($clan);
		$this->payload->clanId = $this->getPlayerClan()->id;
		$this->payload->allianceId = ($this->getPlayerClan()->alliance != null) ? $this->getPlayerClan()->alliance->id : null;
		$this->sendPayload();
	}


	public function handleFetchFacilities ()
	{
		$facilities = array();
		$stats = $this->context->stats->construction;
		$clan = $this->getPlayerClan();
		foreach ($stats->getAvailableFacilities($this->getPlayerClan()) as $facility) {
			$facilities[$facility] = array();
			$facilities[$facility]['cost'] = $stats->getConstructionCost($clan, $facility, 1);
			$facilities[$facility]['time'] = $stats->getConstructionTime($clan, $facility, 1);
		}
		$this->payload->facilities = $facilities;
		$changes = $this->getFacilityRepository()->getAvailableFacilityChanges($clan);
		$this->payload->upgrades = $changes['upgrades'];
		$this->payload->downgrades = $changes['downgrades'];
		$this->payload->repairs = $changes['repairs'];
		$this->payload->demolitions = $changes['demolitions'];
		$this->sendPayload();
	}

	public function handleSendAttack ($originId, $targetId, $type)
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
			$this->getMoveService()->startUnitMovement($origin, $target, $this->getPlayerClan(), $type, $units);
			$this->invalidateControl('orders');
			$this->invalidateControl('events');
			$this->flashMessage('Útok zahájen');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Takový útok není možný', 'error');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		}
	}

	public function handleSendSpy ($originId, $targetId, $type)
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
			$this->getMoveService()->startUnitMovement($origin, $target, $this->getPlayerClan(), $type, $units);
			$this->invalidateControl('orders');
			$this->invalidateControl('events');
			$this->flashMessage('Špionáž zahájena');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Taková špionáž není možná', 'error');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		}
	}

	public function handleFetchColonisationCost ($targetId)
	{
		$target = $this->getFieldRepository()->find($targetId);
		$clan = $this->getPlayerClan();
		$cost = $this->context->stats->colonisation->getCost($target, $clan);
		$this->payload->cost = $cost;
		$this->payload->time = $this->context->stats->colonisation->getTime($target, $clan);
		$this->sendPayload();
	}

	public function handleSendColonisation ($targetId)
	{
		try {
			$this->context->model->getConstructionService()->startColonisation($this->context->model->getFieldRepository()->find($targetId), $this->getPlayerClan());
			$this->invalidateControl('orders');
			$this->invalidateControl('resources');
			$this->invalidateControl('events');
			$this->flashMessage('Kolonizace zahájena');
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Toto pole už kolonizujete.', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze kolonizovat pole, se kterým nesousedíte', 'error');
		} catch (InsufficientResourcesException $e) {
			$this->flashMessage('Nemáte dostatek surovin', 'error');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		}
	}

	public function handleLeaveField ($targetId)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		try {
			$this->context->model->getConstructionService()->startAbandonment($target);
			$this->invalidateControl('orders');
			$this->invalidateControl('events');
			$this->flashMessage('Opuštení zahájeno');
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Nelze opustit pole, na kterém právě probíhá stavba', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze opustit pole, které vám nepatří.', 'error');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		}
	}

	public function handleBuildFacility ($targetId, $facility)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		try {
			$this->context->model->getConstructionService()->startFacilityConstruction($target, $facility);
			$this->invalidateControl('orders');
			$this->invalidateControl('resources');
			$this->invalidateControl('events');
			$this->flashMessage('Stavba zahájena');
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Na tomto poli už probíhá nějaká stavba', 'error');
		} catch (InsufficientResourcesException $e) {
			$this->flashMessage('Nemáte dostatek surovin', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze stavět na cizím, nebo zastaveném poli', 'error');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		} catch (MissingDependencyException $e){
			$this->flashMessage('Nemáte vyzkoumáno vše potřebné', 'error');
		}
	}

	public function handleRepairFacility ($targetId)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		try {
			$this->context->model->getConstructionService()->startFacilityRepair($target);
			$this->invalidateControl('orders');
			$this->invalidateControl('resources');
			$this->invalidateControl('events');
			$this->flashMessage('Stavba zahájena');
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Na tomto poli už probíhá nějaká stavba', 'error');
		} catch (InsufficientResourcesException $e) {
			$this->flashMessage('Nemáte dostatek surovin', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze stavět na cizím, nebo zastaveném poli', 'error');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		}
	}

	public function handleDestroyFacility ($targetId)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		try {
			$this->context->model->getConstructionService()->startFacilityDemolition($target, 0);
			$this->invalidateControl('orders');
			$this->invalidateControl('resources');
			$this->invalidateControl('events');
			$this->flashMessage('Demolice zahájena');
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Na tomto poli už probíhá nějaká stavba', 'error');
		} catch (InsufficientResourcesException $e) {
			$this->flashMessage('Nemáte dostatek surovin', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze bourat na cizím, nebo nezastaveném poli', 'error');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		}
	}

	public function handleUpgradeFacility ($targetId)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		try {
			$this->context->model->getConstructionService()->startFacilityConstruction($target, $target->facility->type, $target->facility->level + 1);
			$this->invalidateControl('orders');
			$this->invalidateControl('resources');
			$this->invalidateControl('events');
			$this->flashMessage('Stavba zahájena');
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Na tomto poli už probíhá nějaká stavba', 'error');
		} catch (InsufficientResourcesException $e) {
			$this->flashMessage('Nemáte dostatek surovin', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze stavět na cizím, nebo zastaveném poli', 'error');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		} catch (MissingDependencyException $e){
			$this->flashMessage('Nemáte vyzkoumáno vše potřebné', 'error');
		}
	}

	public function handleDowngradeFacility ($targetId)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		try {
			$this->context->model->getConstructionService()->startFacilityDemolition($target, $target->facility->level - 1);
			$this->invalidateControl('orders');
			$this->invalidateControl('resources');
			$this->invalidateControl('events');
			$this->flashMessage('Demolice zahájena');
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Na tomto poli už probíhá nějaká stavba', 'error');
		} catch (InsufficientResourcesException $e) {
			$this->flashMessage('Nemáte dostatek surovin', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nelze stavět na cizím, nebo zastaveném poli', 'error');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		}
	}

	public function handleSendExploration ($originId, $targetId)
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
			$this->context->model->getMoveService()->startUnitMovement($origin, $target, $this->getPlayerClan(), 'exploration', $units);
			$this->invalidateControl('orders');
			$this->invalidateControl('events');
			$this->flashMessage('Průzkum zahájen');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Toto pole nemůžete prozkoumávat', 'error');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		}
	}

	public function handleRedirect ($target = 'this')
	{
		$this->redirect($target);
	}

	public function handleMoveUnits ($originId, $targetId)
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
			$this->getMoveService()->startUnitMovement($origin, $target, $this->getPlayerClan(), 'unitMovement', $units);
			$this->invalidateControl('orders');
			$this->invalidateControl('events');
			$this->flashMessage('Přesun zahájen');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Takový přesun není možný', 'error');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		}


	}

}
