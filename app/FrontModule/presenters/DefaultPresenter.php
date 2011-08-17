<?php
namespace FrontModule;
use Nette;

class DefaultPresenter extends BasePresenter
{

	protected function createComponentLoginForm()
	{
		$form = new Form;

		$form->addText('login', 'Login')
			->setRequired('Zadejte prosím přihlašovací jméno');

		$form->addPassword('password', 'Heslo')
			->setRequired('Zadejte prosím heslo');
		
		$form->addSubmit('send', 'Přihlásit');

		//$form->setTranslator($translator);
		
		$form->onSuccess[] = callback($this, 'submitLoginForm');
		
		return $form;
	}

	public function submitLoginForm($form)
	{
		$data = $form->getValues();	

	}

}
