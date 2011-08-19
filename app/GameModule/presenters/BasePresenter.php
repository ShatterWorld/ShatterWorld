<?php
namespace GameModule;
use Nette;

abstract class BasePresenter extends \BasePresenter
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
		$this->getUser()->logout(TRUE);
		$this->flashMessage("Odhlášen");
		$this->redirect(':Front:Homepage:');
	}

	public function getPlayerProfile ()
	{
		return $this->getProfileRepository()->findOneByUser($this->getUser()->getId());
	}


}
