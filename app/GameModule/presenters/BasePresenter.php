<?php
namespace GameModule;
use Nette;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{

		public function handleLogout(){
			$this->getUser()->logout();
			$this->flashMessage("Odhlášen");
			$this->redirect(':Front:Default:');
		}
}
