<?php
namespace FrontModule;
use Nette;
use Nette\Application\UI\Form;
class SignupPresenter extends BasePresenter
{

	protected function createComponentSignupForm ()
	{

		$form = new Form;

		$form->addText('login', 'Přihlašovací jméno')
			->setRequired('Zadejte prosím unikátní login');

		$form->addText('nickname', 'Herní jméno')
			->setRequired('Zadejte prosím unikátní přezdívku');

		$form->addText('email', 'E-mail')
			->setRequired('Zadejte prosím platný e-mail')
			->addRule(Form::EMAIL, 'Neplatná e-mailová adresa');

		$form->addPassword('password', 'Heslo')
			->setRequired('Zadejte prosím heslo')
			->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 5);

		$form->addPassword('passwordVerify', 'Heslo (kontrola)')
			->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
			->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);

		$form->addCheckbox('agree1', 'Souhlasím s Všeobecnými a licenčnimi podmínkami.')
			->addRule(Form::EQUAL, 'Je potřeba souhlasit s podmínkami', TRUE);

		$form->addCheckbox('agree2', 'Souhlasím s Všeobecnými herními pravidly.')
			->addRule(Form::EQUAL, 'Je potřeba souhlasit s pravidly', TRUE);

		$form->addCheckbox('agree3', 'Souhlasím s Prohlášením o ochraně osobních údajů.')
			->addRule(Form::EQUAL, 'Je potřeba souhlasit s prohlášením', TRUE);

		$form->addSubmit('send', 'Registrovat');

		//$form->setTranslator($translator);


		$form->onSuccess[] = callback($this, 'submitSignupForm');

		return $form;
	}

	public function submitSignupForm ($form)
	{
		$data = $form->getValues();
		$data['role'] = 'player';
		$this->getService('userService')->create($data);

		$this->flashMessage("Vaše registrace proběhla úspěšně");

		$this->redirect('Homepage:');

	}


}
