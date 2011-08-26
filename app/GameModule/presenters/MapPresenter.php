<?php
namespace GameModule;
use Nette;
use Nette\Diagnostics\Debugger;

class MapPresenter extends BasePresenter {
	public function renderDefault ()
	{
		$mapOptions = $this->context->params['game']['map'];
		$this->template->sizeX = $mapOptions['sizeX'];
		$this->template->sizeY = $mapOptions['sizeY'];
		//$this->template->fields = $this->getFieldRepository()->getVisibleFields($this->getPlayerClan()->getId(), 3);
		$this->template->fields = $this->getFieldRepository()->getMap();
		$this->template->clan = $this->getPlayerClan();

		//Debugger::barDump($this->getFieldRepository()->findCircuit($this->getFieldRepository()->findByCoords(8, 7), 3));

	}
}
