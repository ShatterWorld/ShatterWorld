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
		$this->getUnitService()->deleteAll(FALSE);
		$this->getReportService()->deleteAll(FALSE);
		$this->getEventService()->deleteAll(FALSE);
		$this->getResourceService()->deleteAll(FALSE);
		$this->getOfferService()->deleteAll(FALSE);
		$this->getAllianceService()->deleteAll(FALSE);
		$this->getClanService()->deleteAll(FALSE);
		$this->getFieldService()->deleteAll(TRUE);
		$this->getFieldRepository()->getVisibleFieldsCache()->clean();
		$this->getClanRepository()->getVisibleClansCache()->clean();
		$this->getFieldService()->createMap();
		$this->flashMessage('Nová mapa byla vygenerována');
		$this->redirect('default');
	}
}
