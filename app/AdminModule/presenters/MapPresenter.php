<?php
namespace AdminModule;
use Nette;
use Nette\Application\UI\Form;

class MapPresenter extends BasePresenter {
	protected function createComponentGenerateMapForm ()
	{
		$form = new Form;
		$form->addSubmit('generate', 'Generovat mapu');
		$form->onSuccess[] = callback($this, 'submitGenerateMapForm');
		return $form;
	}

	public function submitGenerateMapForm (Form $form)
	{
		$this->getEventService()->deleteAll();
		$this->getResourceService()->deleteAll();
		$this->getAllianceService()->deleteAll();
		$this->getClanService()->deleteAll();
		$this->getFieldService()->deleteAll();
		$this->getFieldService()->createMap();
		$this->flashMessage('Nová mapa byla vygenerována');
		$this->redirect('default');
	}
}
