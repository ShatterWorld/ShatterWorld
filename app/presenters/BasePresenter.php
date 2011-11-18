<?php
use Nette\Utils\Strings;
use Nette\Application\UI\Form;
use Nette\Security\IAuthenticator;

/**
 * Base class for all application presenters.
 *
 * @author     Jan "Teyras" Buchar
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	public function __call ($name, $args)
	{
		if (Strings::startsWith($name, 'get') && (Strings::endsWith($name, 'Service') || Strings::endsWith($name, 'Repository'))) {
			return $this->getService('model')->$name();
		}
		return parent::__call($name, $args);
	}

	public function beforeRender ()
	{
		$this->template->registerHelper('json', callback($this, 'jsonEncode'));
		$this->template->registerHelper('time', callback($this, 'getTimeFormat'));
	}

	protected function createComponentLoginForm ()
	{
		$form = new Form;

		$form->addText('login', 'Login', 13)
			->setRequired('Zadejte prosím přihlašovací jméno');

		$form->addPassword('password', 'Heslo', 13)
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

	public function jsonEncode ($value)
	{
		return Nette\Utils\Json::encode($value);
	}

	public function getTimeFormat ($t)
	{
		$h = floor($t / 3600);
		$t -= $h*3600;
		$m = floor($t / 60);
		$t -= $m*60;
		$s = $t;
		return sprintf('%d:%02d:%02d', $h, $m, $s);
	}
}





