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
		if ($this->getAction(TRUE) !== ':Game:Clan:new' && !$this->getPlayerClan()) {
 			$this->redirect('Clan:new');
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

	public function flashMessage ($message, $type = 'info')
	{
		$this->invalidateControl('flashes');
		parent::flashMessage($message, $type);
	}
	
	/**
	 * Send resources list via ajax
	 * @return void
	 */
	public function handleFetchResources ()
	{
		$this->payload->resources = $this->getResourceRepository()->getResourcesArray($this->getPlayerClan());
		$this->sendPayload();
	}
	
	/**
	 * Send upcoming events via ajax
	 * @return void
	 */
	public function handleFetchEvents ()
	{
		$this->payload->events = $this->getEventRepository()->getUpcomingEventsArray($this->getPlayerClan());
		$this->sendPayload();
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

		$this->redirect('Map:');
	}

	public function handleCreate10Users ()
	{
		for($i = 0; $i < 10; $i++){
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
		);		}
		$this->redirect('Map:');
	}

	public function handleCreate100Users ()
	{
		for($i = 0; $i < 100; $i++){
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
		);		}
		$this->redirect('Map:');
	}


}
