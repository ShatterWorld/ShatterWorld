<?php
namespace GameModule;
use Nette;

class MapPresenter extends BasePresenter {
	public function renderDefault ()
	{
		$mapOptions = $this->context->params['game']['map'];
		$this->template->sizeX = $mapOptions['sizeX'];
		$this->template->sizeY = $mapOptions['sizeY'];
		$this->template->fields = $this->getFieldRepository()->getVisibleFields($this->getPlayerClan()->getId(), 1);				
		//$this->template->fields = $this->getFieldRepository()->getMap();				
	}
}
