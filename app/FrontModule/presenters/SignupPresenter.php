<?php
namespace FrontModule;
use Nette;

class SignupPresenter extends BasePresenter
{

	protected function createComponentSignupForm()
	{
		$form = new Nette\Application\UI\Form;
		$form->addText('name', 'Jméno:');
		$form->addText('email', 'E-mail:');
		$form->addPassword('password', 'Heslo:');
		$form->addPassword('password2', 'Heslo (pro kontrolu):');
		$form->addText('landName', 'Jméno země:');
		$form->addSubmit('send', 'Přihlásit');

		//$form->setTranslator($translator);
		
		return $form;
	}


}
