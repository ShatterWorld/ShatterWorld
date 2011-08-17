<?php
namespace FrontModule;
use Nette;

class DefaultPresenter extends BasePresenter
{

	protected function createComponentLoginForm()
	{
		$form = new Nette\Application\UI\Form;
		$form->addText('name', 'Jméno');
		$form->addPassword('password', 'Heslo');
		$form->addSubmit('send', 'Přihlásit');
		//$form->setTranslator($translator);
		
		return $form;
	}

}
