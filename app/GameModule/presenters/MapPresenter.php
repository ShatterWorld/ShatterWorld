<?php
namespace GameModule;
use Nette;

class MapPresenter extends BasePresenter {
	public function renderDefault ()
	{
		$this->template->fields = $this->getFieldRepository()->getMap();
	}
}