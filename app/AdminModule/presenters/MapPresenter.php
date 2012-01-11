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
		$this->getFacilityService()->deleteAll(FALSE);
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
		$fields = $this->context->model->fieldRepository->getMapArray();
		$this->payload->fields = $fields;
		$cache = new Nette\Caching\Cache($this->context->cacheStorage, 'Map');
		$this->payload->mapSize = count($fields);
		$this->payload->ranks = $cache->load('FieldRanks');
		$this->payload->maxRank = $this->context->map->getHighestRankedField();
		$this->sendPayload();
	}

	protected function createComponentSwitchOverlayForm ()
	{
		$form = new Form;
		$form->addSelect('overlay', 'Pohled', array(
			'default' => 'Výchozí',
			'rank' => 'Rozmísťování klanů'
		));
		return $form;
	}
	
	protected function createComponentGenerateClanForm ()
	{
		$form = new Form;
		$form->addText('count', 'Počet klanů')->addRule(Form::INTEGER, 'Počet musí být číslo');
		$form->addSubmit('generate', 'Generovat');
		$form->onSuccess[] = callback($this, 'submitGenerateClanForm');
		$form->setDefaults(array('count' => 1));
		return $form;
	}
	
	public function submitGenerateClanForm (Form $form)
	{
		$data = $form->getValues();
		for ($i = 0; $i < $data['count']; $i++) {
			$this->getService('clanService')->create(array(
				'name' => Nette\Utils\Strings::random(),
				'user' => $this->context->model->userRepository->getActiveUser()
			), FALSE);
		}
		$this->context->entityManager->flush();
	}
}
