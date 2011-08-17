<?php
namespace FrontModule;
use Nette;

class DefaultPresenter extends BasePresenter
{

	protected function createComponentLoginForm()
	{
		$form = new Nette\Application\UI\Form;

		$form->addText('login', 'Login')
			->setRequired('Zadejte prosím přihlašovací jméno');

		$form->addPassword('password', 'Heslo')
			->setRequired('Zadejte prosím heslo');
		
		$form->addSubmit('send', 'Přihlásit');

		//$form->setTranslator($translator);
		//$form->setAction('/submit.php');
		//$form->setMethod('POST');
		
		return $form;
	}

}
