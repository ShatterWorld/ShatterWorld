<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;

class ClanPresenter extends BasePresenter {
	
	public function renderDefault ()
	{
		$this->template->clan = $this->getPlayerClan();
	}
	
	public function actionAdd ()
	{
		if ($this->getPlayerClan()) {
			$this->redirect('Clan:');
		}
	}
	
	protected function createComponentNewClanForm () 
	{
		$form = new Form();
		$form->addText('name', 'Jméno klanu')
			->setRequired('Vyplňte, prosím, jméno klanu.');
		$form->addSubmit('submit', 'Založit klan');
		$form->onSuccess[] = callback($this, 'submitNewClanForm');
		return $form;
	}
	
	public function submitNewClanForm (Form $form)
	{
		if (!$this->getPlayerClan()) {
			$data = $form->getValues();
			$data['user'] = $this->getUserRepository()->find($this->getUser()->getId());
			$this->getService('clanService')->create($data);
			$this->flashMessage(sprintf('Klan %s byl založen.', $data['name']));
		}
		$this->redirect('Clan:');
	}
	
	protected function getPlayerClan ()
	{
		return $this->getClanRepository()->findOneByUser($this->getUser()->getId());
	}
	
}