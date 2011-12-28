<?php
namespace AdminModule;
use Nette;
use Nette\Caching\Cache;
use Nette\Application\UI\Form;

class MapPresenter extends BasePresenter
{
	protected function createComponentGenerateMapForm ()
	{
		$form = new Form;
		$form->addSubmit('generate', 'Generovat mapu');
		$form->onSuccess[] = callback($this, 'submitGenerateMapForm');
		return $form;
	}

	public function submitGenerateMapForm (Form $form)
	{
		$this->getClanService()->deleteAll(FALSE);
		$this->getFieldService()->deleteAll(TRUE);

		$this->getFieldRepository()->getVisibleFieldsCache()->clean(array(Cache::ALL => TRUE));
		$this->getClanRepository()->getVisibleClansCache()->clean(array(Cache::ALL => TRUE));
		$this->getClanRepository()->getClanGraphCache()->clean(array(Cache::ALL => TRUE));
		$this->getClanService()->getCache()->clean(array(Cache::ALL => TRUE));

		$this->getFieldService()->createMap();
		$this->flashMessage('Nová mapa byla vygenerována');
		$this->redirect('default');
	}
	
	public function handleFetchMap ()
	{
		$this->payload->fields = $this->context->model->fieldRepository->getMapArray();
		$cache = new Nette\Caching\Cache($this->context->cacheStorage, 'Map');
		$this->payload->ranks = $cache->load('FieldRanks');
		$this->sendPayload();
	}
}
