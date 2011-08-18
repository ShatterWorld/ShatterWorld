<?php
namespace FrontModule;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\IAuthenticator;

class HomepagePresenter extends BasePresenter {
	protected function createComponentLoginForm ()
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

	public function submitLoginForm ($form)
	{
		$data = $form->getValues();
		try {
			$this->getUser()->login($data["login"], $data["password"]);
		} 
		catch (Nette\Security\AuthenticationException $e) {
			if ($e->getCode() === IAuthenticator::INVALID_CREDENTIAL) {
				$this->flashMessage("Špatné heslo", "error");
			} elseif ($e->getCode() === IAuthenticator::IDENTITY_NOT_FOUND) {
				$this->flashMessage("Špatný login", "error");
			} else {
				$this->flashMessage("Neočekávaná chyba", "error");
			}
			$this->redirect('this');
		}
		$this->flashMessage("Úspěšně přihlášen");
		$this->redirect(":Game:Dashboard:");
	}
}
