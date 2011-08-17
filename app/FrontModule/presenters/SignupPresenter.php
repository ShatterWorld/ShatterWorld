<?php
namespace FrontModule;
use Nette;
use Nette\Application\UI\Form;
class SignupPresenter extends BasePresenter
{

	protected function createComponentSignupForm()
	{
	
		/*TODO: add somewhere..?
		
		<script src="netteForms.js"></script>
		<style>
			.required label { color: maroon }
		</style>
		*/

		
		$form = new Nette\Application\UI\Form;

		$form->addGroup("Osobní");
			$form->addText('name', 'Jméno')
				->setRequired('Zadejte prosím jméno');

			$form->addText('age', 'Věk')
				->addRule(Form::INTEGER, 'Věk musí být číslo')
				->addRule(Form::RANGE, 'Věk musí být od %d do %d let', array(7, 101));

			$sex = array(
				'm' => 'muž',
				'f' => 'žena',
			);
			$form->addRadioList('gender', 'Pohlaví:', $sex);

		$form->addGroup("Hra");
			$form->addText('login', 'Login')
				->setRequired('Zadejte prosím unikátní login');
				
			$form->addText('email', 'E-mail')
				->setRequired('Zadejte prosím platný e-mail')
				->addRule(Form::EMAIL, 'Neplatná e-mailová adresa');
				
			$form->addPassword('password', 'Heslo')
				->setRequired('Zadejte prosím heslo')
				->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 5);
				
			$form->addPassword('passwordVerify', 'Heslo (pro kontrolu)')
				->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
				->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);		
				
			/*$form->addText('landName', 'Jméno země')
				->setRequired('Zadejte prosím unikátní jméno země');
*/
		$form->addGroup("Podmínky");

			$form->addTextArea("conditions", "Podmínky")
				->setDefaultValue("lorem \nipsum \ndolor \nsit...\n ahoj\n ahoj\n ahoj\n ahoj\n ahoj\n ahoj\n ahoj\n ahojahoj\n ahoj\nahoj\n ahoj\nahoj\n ahoj\n")
				->setDisabled();
			
			$form->addCheckbox("agree", "Souhlasím s podmínkami")
				->addRule(Form::EQUAL, 'Je potřeba souhlasit s podmínkami', TRUE);
			
			$form->addSubmit('send', 'Registrovat');

		//$form->setTranslator($translator);
		
		
		$form->onSuccess[] = callback($this, 'submitSignupForm');
		
		return $form;
	}

	public function submitSignupForm($form)
	{
		$data=$form->getValues();	

	}
	
	
}
