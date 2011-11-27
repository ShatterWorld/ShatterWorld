<?php
namespace GameModule;
use Nette;

abstract class BasePresenter extends \BasePresenter
{
	/**
	 * Startup
	 * @return void
	 */
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

	/**
	 * Before render
	 * @return void
	 */
	public function beforeRender ()
	{
		parent::beforeRender();
		$clan = $this->getPlayerClan();
		if ($clan !== null){
			$this->template->orderCount = $this->context->stats->getAvailableOrders($clan);
			$this->template->reportCount = $this->getReportRepository()->countUnread($clan);
			$this->template->resources = $this->getResourceRepository()->getResourcesArray($clan);
			$this->template->resourceRules = $this->context->rules->getAll('resource');
			$this->template->events = $this->getEventRepository()->getUpcomingEventsArray($clan);
			ksort($this->template->resources);
		} else {
			$this->template->orderCount = 0;
			$this->template->reportCount = 0;
			$this->template->events = array();
			$this->template->resources = array();
		}
		$this->template->orderCap = $this->context->params['game']['stats']['orderCap'];
	}

	/**
	 * Logout signal
	 * @return void
	 */
	public function handleLogout ()
	{
		$this->getUser()->logout(TRUE);
		$this->flashMessage("Odhlášen");
		$this->redirect(':Front:Homepage:');
	}

	protected function createComponentChooseClanForm ()
	{
		$form = new Nette\Application\UI\Form;
		$clans = array();
		foreach ($this->getContext()->model->getUserRepository()->getActiveUser()->getClans() as $clan) {
			$clans[$clan->id] = $clan->name;
		}
		$form->addSelect('clan', NULL, $clans);
		$form['clan']->setValue($this->getPlayerClan()->id);
		return $form;
	}
	
	public function handleSwitchClan ($clanId)
	{
		$model = $this->getContext()->model;
		$clan = $model->getClanRepository()->find($clanId);
		$user = $model->getUserRepository()->getActiveUser();
		if ($clan->user === $user) {
			$model->getUserService()->update($user, array('activeClan' => $clan));
		}
		$this->redirect('this');
	}
	
	/**
	 * Returns players profile
	 * @return Entities\Profile
	 */
	public function getPlayerProfile ()
	{
		return $this->getProfileRepository()->findOneByUser($this->getUser()->getId());
	}

	/**
	 * Returns players clan
	 * @return Entities\Clan
	 */
	public function getPlayerClan ()
	{
		return $this->getClanRepository()->getPlayerClan();
	}

	/**
	 * Returns players units
	 * @return array of Entities\Unit
	 */
	public function getClanUnits ()
	{
		return $this->getUnitRepository()->findByOwner($this->getPlayerClan()->getId());
	}

	/**
	 * Returns players offers
	 * @return array of Entities\Offer
	 */
	public function getClanOffers ()
	{
		return $this->getOfferRepository()->findByOwner($this->getPlayerClan()->getId());
	}

	/**
	 * Flash message
	 * @return void
	 */
	public function flashMessage ($message, $type = 'info')
	{
		$this->invalidateControl('flashes');
		parent::flashMessage($message, $type);
	}

	/**
	 * Returns count of players unread messages
	 * @return int
	 */
	public function getUnreadMsgCount ()
	{
		return $this->getMessageRepository()->getUnreadMsgCount($this->getPlayerClan()->user->id);
	}

	/**
	 * Returns true if the clan is the leder of its alliance, false otherwise
	 * @return bool
	 */
	public function isLeader ()
	{
		return $this->getPlayerClan()->getAlliance()->leader === $this->getPlayerClan();
	}


	/**
	 * Signal sending upcoming events via ajax
	 * @return void
	 */
	public function handleFetchEvents ()
	{
		$this->invalidateControl('events');
	}

	/**
	 * Ajax descriptionfetching
	 * @return void
	 */
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
				'login' => Nette\Utils\Strings::random(),
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
				'login' => Nette\Utils\Strings::random(),
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
				'login' => Nette\Utils\Strings::random(),
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
