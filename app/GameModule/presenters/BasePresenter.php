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

		$this->template->orderCount = $clan ? $this->context->stats->orders->getAvailableOrders($clan) : 0;
		$this->template->orderCap = $this->context->params['game']['stats']['orderCap'];
		$this->template->orderTimeout = $this->context->stats->orders->getTimeout();

		$this->template->reportCount = $clan ? $this->getReportRepository()->countUnread($clan) : 0;

		$this->template->resources = $clan ? $this->getResourceRepository()->getResourcesArray($clan) : array();
		$this->template->resourceRules = $clan ? $this->context->rules->getAll('resource') : array();

		$this->template->events = $clan ? $this->getEventRepository()->findUpcomingEvents($clan) : array();
		$this->template->eventData = $clan ? array_map(function ($event) {
			$result = $event->toArray();
			$result['target'] = $event->target->toArray();
			return $result;
		}, $this->template->events) : array();
		$this->template->eventRules = $this->context->rules->getAll('event');

		ksort($this->template->resources);
	}

	protected function createComponentChooseClanForm ()
	{
		$form = new Nette\Application\UI\Form;
		$clans = array();
		foreach ($this->getContext()->model->getUserRepository()->getActiveUser()->getClans() as $clan) {
			if (!$clan->deleted) {
				$clans[$clan->id] = $clan->name;
			}
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
	 * Returns clan alliance
	 * @return Entities\Clan
	 */
	public function getClanAlliance ()
	{
		return $this->getPlayerClan()->alliance;
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
		return $this->getOfferRepository()->findBy(array(
			'owner' => $this->getPlayerClan()->getId(),
			'sold' => false
		));
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
		$this->invalidateControl('reports');
		$this->invalidateControl('resources');
	}

	/**
	 * Signal sending order count via ajax
	 * @return void
	 */
	public function handleFetchOrders ()
	{
		$this->invalidateControl('orders');
	}

	/**
	 * Ajax description fetching
	 * @return void
	 */
	public function handleFetchDescriptions ()
	{
		$this->payload->descriptions = $this->context->rules->getDescriptions();
		$this->sendPayload();
	}
}
