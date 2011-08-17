<?php
namespace GameModule;
use Nette;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	public function startup ()
	{
		parent::startup();
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect(':Front:Homepage:');
		}
	}
	
	public function handleLogout () 
	{
		$user = $this->getUser();
		$user->logout();
		$this->flashMessage("Odhlášen");
		$this->redirect(':Front:Homepage:');
	}
}
