<?php
namespace GameModule;
use Nette;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{

		public function handleLogout(){
			$user = $this->getUser();
			$user->logout();
			$this->flashMessage("Odhlášen");
			$this->redirect(':Front:Default:');
		}
}
