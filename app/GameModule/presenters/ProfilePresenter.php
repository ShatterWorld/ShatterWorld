<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;

/**
 * A ProfilePresenter
 * @author Petr Bělohlávek
 */
class ProfilePresenter extends BasePresenter {
	/**
	* Redirects if user has no profile, otherwise displays his profile
	* @return void
	*/
	public function renderDefault ()
	{
		if (!$this->getPlayerProfile()) {
			$this->redirect('Profile:new');
		}	
		$this->template->profile = $this->getPlayerProfile();
	}
	
	/**
	* Action for new profile
	* @return void
	*/
	public function actionNew ()
	{
		if ($this->getPlayerProfile()) {
			$this->redirect('Profile:');
		}
	}
		
	/**
	* Action for editation of the profile
	* @return void
	*/
	public function renderEdit ()
	{
		$this['editProfileForm']->setValues($this->getPlayerProfile()->toArray());
	}
	
	/**
	* Creates the New form
	* @return Nette\Application\UI\Form
	*/
	protected function createComponentNewProfileForm () 
	{
		$form = new Form();
		$form->addSubmit('submit', 'Založit profil');
		$form->onSuccess[] = callback($this, 'submitNewProfileForm');
		return $form;
	}
	
	/**
	* New profile slot
	* @param Nette\Application\UI\Form
	* @return void
	*/
	public function submitNewProfileForm (Form $form)
	{
		if (!$this->getPlayerProfile()) {
			$this->getProfileService()->create(array('user' => $this->getUserRepository()->find($this->getUser()->getId())));
			$this->flashMessage('Profil byl založen.');
		}
		$this->redirect('Profile:edit');
		
	}
	
	/**
	* Creates the Edit form
	* @return Nette\Application\UI\Form
	*/
	protected function createComponentEditProfileForm () 
	{
		$form = new Form();
		
		$form->addUpload('avatar', 'Avatar (50kB)')
			->addCondition(Form::FILLED)
				->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost souboru nesmí být větší než 50kB', 50*1024)
				->addRule(Form::IMAGE, 'Avatar musí být JPEG, PNG nebo GIF');
			
		$form->addText('name', 'Jméno');
		
		$form->addText('age', 'Věk')
			->addCondition(Form::FILLED)
				->addRule(Form::INTEGER, 'Věk musí být číslo');
		
		$sex = array(
			'm' => 'muž',
			'f' => 'žena',
		);
		$form->addRadioList('gender', 'Pohlaví', $sex);

		$form->addText('icq', 'ICQ');
				
		$form->addSubmit('submit', 'Uložit');
		$form->onSuccess[] = callback($this, 'submitEditProfileForm');
		return $form;
	}
	
	/**
	* Edit profile slot
	* @param Nette\Application\UI\Form
	* @return void
	*/
	public function submitEditProfileForm (Form $form)
	{
		if ($this->getPlayerProfile()) {
			$this->getProfileService()->update($this->getPlayerProfile(), $form->getValues());
			$this->flashMessage('Uloženo!');
		}
		//$this->redirect('Profile:');
		
	}

	/**
	* Creates the Delete form
	* @return Nette\Application\UI\Form
	*/
	protected function createComponentDeleteProfileForm () 
	{
		$form = new Form();
		$form->addSubmit('submit', 'Smazat profil');
		$form->onSuccess[] = callback($this, 'submitDeleteProfileForm');
		return $form;
	}
	
	/**
	* Delete profile slot
	* @param Nette\Application\UI\Form
	* @return void
	*/
	public function submitDeleteProfileForm (Form $form)
	{
		if ($this->getPlayerProfile()) {
			$this->getProfileService()->delete($this->getPlayerProfile());
			$this->flashMessage('Profil byl smazán.');
		}
		$this->redirect('Profile:new');
		
	}

	
	
}
