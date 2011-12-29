<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;

/**
 * Profile Presenter
 * @author Petr Bělohlávek
 */
class ProfilePresenter extends BasePresenter {

	/**
	* Show or create a profile
	* @return void
	*/
	public function actionDefault ()
	{
		$this->redirect('Profile:show', $this->getPlayerClan()->user->id);
	}

	/**
	* Show action
	* @param int
	* @return void
	*/
	public function actionShow ($userId = null)
	{
		if ($userId === null){
			$this->redirect('Profile:show', $this->getPlayerClan()->user->id);
		}
	}

	/**
	* Show render
	* @param int
	* @return void
	*/
	public function renderShow ($userId)
	{
		if ($profile = $this->context->model->getProfileRepository()->findOneByUser($userId)){
			$this->template->profile = $profile;
			$this->template->clan = $this->getPlayerClan();
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
		$this->redirect('Profile:');

	}




}
