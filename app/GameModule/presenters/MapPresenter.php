<?php
namespace GameModule;
use Nette;
use Nette\Diagnostics\Debugger;

class MapPresenter extends BasePresenter {
	public function renderDefault ()
	{
		$this->template->size = $this->context->params['game']['map']['size'];
		#$this->template->fields = $this->getFieldRepository()->getVisibleFields($this->getPlayerClan()->getId(), 1);
		$this->template->clan = $this->getPlayerClan();
	}

	public function handleFetchMap ()
	{
		$this->payload->fields = $this->getFieldRepository()->getVisibleFieldsArray($this->getPlayerClan()->id, 10);
		$this->payload->clanId = $this->getPlayerClan()->id;
		$this->sendPayload();
	}

}
