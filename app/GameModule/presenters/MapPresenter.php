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
		$this->payload->allianceId = $this->getPlayerClan()->alliance->id;
		$this->sendPayload();
	}

	public function handleSendColonisation ($originId, $targetId)
	{
		$origin = $this->context->model->getFieldRepository()->find($originId);
		$target = $this->context->model->getFieldRepository()->find($targetId);
		$clan = $this->getPlayerClan();
		if ($origin->owner->id === $clan->id && $target->owner === NULL) {
			$this->context->model->getMoveService()->create(array(
				'origin' => $origin,
				'target' => $target,
				'clan' => $clan,
				'type' => 'colonisation'
			));
		}
	}
}
