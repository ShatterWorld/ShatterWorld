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
		if ($this->getPlayerProfile()) {
			$this->redirect('Profile:');
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
			$this->getProfileService()->create(array('user' => $this->getUserRepository()->find($this->getUser()->getId())));
			$this->flashMessage('Profil byl založen.');
		}
		$this->redirect('Profile:');
		
	}
	
	protected function createComponentEditProfileForm () 
	{
		$form = new Form();
		$form->setValues($this->getPlayerProfile()->toArray());		//!!!
		$form->setDefaults($this->getPlayerProfile()->toArray());		//!!!
		
		$form->addText('name', 'Jméno');
		
		$form->addText('age', 'Věk')
			->addCondition(Form::FILLED)
				->addRule(Form::INTEGER, 'Věk musí být číslo');
		
		$sex = array(
			'm' => 'muž',
			'f' => 'žena',
		);
		$form->addRadioList('gender', 'Pohlaví:', $sex);

		$form->addText('icq', 'ICQ');
				
		$form->addSubmit('submit', 'Uložit');
		$form->onSuccess[] = callback($this, 'submitEditProfileForm');
		return $form;
	}
	
	public function submitEditProfileForm (Form $form)
	{
		$this->getProfileService()->update($this->getPlayerProfile(), $form->getValues());
		$this->flashMessage('Uloženo!');
		$this->redirect('Profile:');
		
	}
	
	protected function getPlayerProfile ()
	{
		return $this->getProfileRepository()->findOneByUser($this->getUser()->getId());
	}

	
}
