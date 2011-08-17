<?php
namespace FrontModule;
use Nette;
use Nette\Application\UI\Form;

class DefaultPresenter extends BasePresenter
{
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
		try{
			$this->getUser()->login($data["login"], $data["password"]);
		}catch (Nette\Security\AuthenticationException $e){
			$code=$e->getCode();
			if ($code == Nette\Security\IAuthenticator::INVALID_CREDENTIAL){
				$this->flashMessage("Špatné heslo", "error");
				
			}else if($code == Nette\Security\IAuthenticator::IDENTITY_NOT_FOUND){
				$this->flashMessage("Špatný login", "error");
				
			}else{
				$this->flashMessage("Nečekávaná chyba", "error");
			}

			$this->redirect('this');
		}
		
		$this->redirect(":Game");


	}

}
