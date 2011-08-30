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
		$this->getAllianceService()->deleteAll();
		$this->getFieldService()->deleteAll();
		$this->getClanService()->deleteAll();
		$this->getFieldService()->createMap();
		$this->flashMessage('Nová mapa byla vygenerována');
		$this->redirect('default');
	}
}
