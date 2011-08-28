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
		$this->context->eventService->processPendingEvents();
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

	public function getPlayerClan ()
	{
		return $this->getClanRepository()->findOneByUser($this->getUser()->getId());
	}

	protected function createComponentMap ()
	{
		$map = new Map();
		$map->setup($this->getFieldRepository, $this->context->params['game']['map']);
		return $map;
	}

	/**
	* Test !!!
	* TODO: remove
	*/
	public function handleCreateUser ()
	{
		$user = $this->getService('userService')->create(
			array(
				'nickname' => Nette\Utils\Strings::random(),
				'email' => 'test@test.com',
				'password' => '123456',
				'role' => 'player'
			)
		);

		$clan = $this->getService('clanService')->create(
			array(
				'name' => Nette\Utils\Strings::random(),
				'user' => $user
			)
		);

		//$this->redirect('Map:');
	}

	public function handleCreate10Users ()
	{
		for($i = 0; $i < 10; $i++){
			$this->handleCreateUser();
		}
		$this->redirect('Map:');
	}




}
