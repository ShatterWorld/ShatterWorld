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

	public function beforeRender ()
	{
		parent::beforeRender();
		$clan = $this->getPlayerClan();
		if ($clan !== null){
			$this->template->orderCount = $this->context->stats->getAvailableOrders($clan);
			$this->template->reportCount = $this->getReportRepository()->countUnread($clan);
			$this->template->resources = $this->getResourceRepository()->getResourcesArray($clan);
			ksort($this->template->resources);
		} else {
			$this->template->orderCount = 0;
			$this->template->reportCount = 0;
			$this->template->resources = array();
		}
		$this->template->orderCap = $this->context->params['game']['stats']['orderCap'];
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
		return $this->getClanRepository()->getPlayerClan();
	}

	public function getClanUnits ()
	{
		return $this->getUnitRepository()->findByOwner($this->getPlayerClan()->getId());
	}

	public function getClanOffers ()
	{
		return $this->getOfferRepository()->findByOwner($this->getPlayerClan()->getId());
	}

	public function flashMessage ($message, $type = 'info')
	{
		$this->invalidateControl('flashes');
		parent::flashMessage($message, $type);
	}

	/**
	 * Send upcoming events via ajax
	 * @return void
	 */
	public function handleFetchEvents ()
	{
		$clan = $this->getPlayerClan();
		$this->payload->events = $clan ? $this->getEventRepository()->getUpcomingEventsArray($clan) : NULL;
		$this->sendPayload();
	}

	public function handleFetchDescriptions ()
	{
		$this->payload->descriptions = $this->context->rules->getDescriptions();
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

		//$this->redirect('Map:');
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
		//$this->redirect('Map:');
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
