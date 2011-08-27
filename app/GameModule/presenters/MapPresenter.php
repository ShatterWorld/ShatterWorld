<?php
namespace GameModule;
use Nette;
use Nette\Diagnostics\Debugger;

class MapPresenter extends BasePresenter {
	public function renderDefault ()
	{
		$this->template->size = $this->context->params['game']['map']['size'];
		$this->template->fields = $this->getFieldRepository()->getVisibleFields($this->getPlayerClan()->getId(), 1);
		//$this->template->fields = $this->getFieldRepository()->getMap();
		$this->template->clan = $this->getPlayerClan();
	}
}
