<?php
namespace GameModule;
use Nette;
use Nette\Diagnostics\Debugger;

class MapPresenter extends BasePresenter
{
	public function renderDefault ()
	{
		$this->template->size = $this->context->params['game']['map']['size'];
		$this->template->clan = $this->getPlayerClan();
	}

	public function handleFetchMap ()
	{
		$clan = $this->getPlayerClan();
		$this->payload->fields = $this->getFieldRepository()->getVisibleFieldsArray($clan->id, $this->context->stats->getVisibilityRadius($clan));
		$this->payload->clanId = $this->getPlayerClan()->id;
		$this->payload->allianceId = ($this->getPlayerClan()->alliance != null) ? $this->getPlayerClan()->alliance->id : null;

		$this->sendPayload();
	}

	public function handleSendColonisation ($targetId)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		$clan = $this->getPlayerClan();
//		if ($origin->owner->id === $clan->id && $target->owner === NULL) {

		$colonisate = false;
		$neighbours = $this->context->model->getFieldRepository()->getFieldNeighbours($target);

		foreach ($neighbours as $neighbour){
			if ($neighbour->owner !== null && $neighbour->owner->id == $clan->id){
				$colonisate = true;
				break;
			}
		}


		if ($colonisate){
			$this->context->model->getMoveService()->create(array(
				'origin' => $clan->getHeadquarters(),
				'target' => $target,
				'clan' => $clan,
				'type' => 'colonisation'
			));
			$this->flashMessage('Kolonizace zahájena');
		}
		else{
			$this->flashMessage('Nelze kolonizovat pole, se kterým nesousedíte', 'error');
		}


	}

	public function handleLeaveField ($targetId)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		$clan = $this->getPlayerClan();

		if ($target->owner !== null && $target->owner->id == $clan->id){
			$this->context->model->getFieldService()->update($target, array(
				'owner' => null,
				'facility' => null,
				'level' => 1,
			));
			$this->flashMessage('Opuštěno');
			$this->redirect('Map:');
		}
		else{
			$this->flashMessage('Nelze opustit pole, které není vaše', 'error');
		}

	}

	public function handleBuildFacility ($targetId, $facility)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		$clan = $this->getPlayerClan();

/*resources check etc*/
		if ($target->owner !== null && $target->owner->id == $clan->id && $target->facility === null){
			$this->context->model->getFieldService()->update($target, array(
				'facility' => $facility,
				'level' => 1,
			));
			$this->flashMessage('Stavba zahájena');
			$this->redirect('Map:');
		}
		else{
			$this->flashMessage('Nelze stavět na cizím, nebo zastaveném poli', 'error');
		}

	}

	public function handleDestroyFacility ($targetId)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		$clan = $this->getPlayerClan();

/*resources check etc*/
		if ($target->owner !== null && $target->owner->id == $clan->id && $target->facility !== null){
			$this->context->model->getFieldService()->update($target, array(
				'facility' => null,
				'level' => 1,
			));
			$this->flashMessage('Stržení zahájeno');
			$this->redirect('Map:');
		}
		else{
			$this->flashMessage('Nelze bourat budovu na cizím, nebo nezastaveném poli', 'error');
		}

	}

	public function handleUpgradeFacility ($targetId)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		$clan = $this->getPlayerClan();

/*resources check etc*/
		if ($target->owner !== null && $target->owner->id == $clan->id && $target->facility !== null){
			$this->context->model->getFieldService()->update($target, array(
				'level' => $target->level + 1,
			));
			$this->flashMessage('Upgrade zahájen');
			$this->redirect('Map:');
		}
		else{
			$this->flashMessage('Nelze upgradovat budovu na cizím, nebo nezastaveném poli', 'error');
		}

	}

	public function handleDowngradeFacility ($targetId)
	{
		$target = $this->context->model->getFieldRepository()->find($targetId);
		$clan = $this->getPlayerClan();

/*resources check etc*/
		if ($target->owner !== null && $target->owner->id == $clan->id && $target->facility !== null && $target->level > 1){
			$this->context->model->getFieldService()->update($target, array(
				'level' => $target->level - 1,
			));
			$this->flashMessage('Downgrade zahájen');
			$this->redirect('Map:');
		}
		else{
			$this->flashMessage('Nelze downgradovat budovu na cizím, nebo nezastaveném poli', 'error');
		}

	}



}
