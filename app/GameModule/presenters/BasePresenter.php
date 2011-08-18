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
		$this->getUser()->logout(TRUE);
		$this->flashMessage("Odhlášen");
		$this->redirect(':Front:Homepage:');
	}
	
	protected function getUserService ()
	{
		return $this->context->getService('userService');
	}
	
	protected function getUserRepository ()
	{
		return $this->getUserService()->getRepository();
	}
	
	protected function getClanService ()
	{
		return $this->context->getService('clanService');
	}
	
	protected function getClanRepository ()
	{
		return $this->getClanService()->getRepository();
	}
}
