<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;

class ProfilePresenter extends BasePresenter {
	
	public function renderDefault ()
	{
		$this->template->profile = $this->getPlayerProfile();
	}
	
	public function actionNew ()
	{
		if ($this->getPlayerClan()) {
			$this->redirect('Clan:');
		}
	}
	
	protected function createComponentNewProfileForm () 
	{
		$form = new Form();
		$form->addSubmit('submit', 'Založit profil');
		$form->onSuccess[] = callback($this, 'submitNewProfileForm');
		return $form;
	}
	
	public function submitNewProfileForm (Form $form)
	{
	
		if (!$this->getPlayerProfile()) {
			$this->flashMessage('Profil byl založen.');
		}
		$this->redirect('Profile:');
		
	}
	
	protected function createComponentEditProfileForm () 
	{
		$form = new Form();
		$form->addText('name', 'Jméno:');
		$form->addSubmit('submit', 'Uložit');
		$form->onSuccess[] = callback($this, 'submitNewProfileForm');
		return $form;
	}
	
	public function submitEditProfileForm (Form $form)
	{
	
		if (!$this->getPlayerProfile()) {
			$this->flashMessage('Uloženo!');
		}
		$this->redirect('Profile:');
		
	}
	
	protected function getPlayerProfile ()
	{
		return $this->getProfileRepository()->findOneByUser($this->getUser()->getId());
	}
	
}
