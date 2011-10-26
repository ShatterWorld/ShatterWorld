<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use InsufficientResourcesException;


/**
 * A MarketPresenter
 * @author Petr Bělohlávek
 */
class MarketPresenter extends BasePresenter {

	/**
	 * Action for buy
	 * @return void
	 */
	public function actionBuy ()
	{
		$offers = $this->getOfferRepository()->findReachable($this->getPlayerClan(), 5);
		$clan = $this->getPlayerClan();
		$clanHq = $clan->getHeadquarters();
		$time = array();
		$hasEnoughRes = array();
		foreach ($offers as $key => $offer){
			$targetHq = $offer->owner->getHeadquarters();
			$time[$key] = $this->getFieldRepository()->calculateDistance($clanHq, $targetHq);
			$hasEnoughRes[$key] = $this->getResourceRepository()->checkResources($clan, array($offer->demand => $offer->demandAmount));
		}

		//Debugger::barDump($offers);
		$this->template->offers = $offers;
		$this->template->time = $time;
		$this->template->hasEnoughRes = $hasEnoughRes;
	}

	/**
	 * Creates the sell form
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSellForm ()
	{
		foreach ($this->context->rules->getAll('resource') as $type => $rule){
			$resources[$type] = $rule->getDescription();
		}

		$form = new Form();

		$form->addGroup('Nabízím');
			$form->addSelect('offer', 'Surovina', $resources)
				->setRequired('Vyberte, prosím, surovinu, kterou chcete prodat.');

			$form->addText('offerAmount', 'Množství')
				->setRequired('Vyplňte, prosím, množství')
				->addRule(Form::INTEGER, 'Musí být číslo!');

		$form->addGroup('Cena');
			$form->addSelect('demand', 'Surovina', $resources)
				->setRequired('Vyberte, prosím, surovinu, kterou chcete prodat.');

			$form->addText('demandAmount', 'Množství')
				->setRequired('Vyplňte, prosím, množství')
				->addRule(Form::INTEGER, 'Musí být číslo!');



		$form->addSubmit('submit', 'Nabídnout');
		$form->onSuccess[] = callback($this, 'submitSellForm');
		return $form;
	}

	/**
	 * Sell slot
	 * @param Nette\Application\UI\Form
	 * @return void
	 */
	public function submitSellForm (Form $form)
	{

		try{
			$data = $form->getValues();
			$data['owner'] = $this->getPlayerClan();
			$this->getOfferService()->create($data);
			$this->flashMessage('Nabídnuto');
		}
		catch(InsufficientResourcesException $e){
			$this->flashMessage('Nedostatek surovin');
		}

		$this->redirect('Market:');
	}


	/**
	 * Displays default overview
	 * @return void
	 */
	public function renderDefault ()
	{
		$this->template->offers = $this->getClanOffers();
	}

	/**
	 * Deletes the given offer
	 * @param int
	 * @return void
	 */
	public function renderDeleteOffer ($offerId)
	{
		$this->getOfferService()->delete($this->getOfferRepository()->findOneById($offerId));
		$this->flashMessage('Staženo z nabídky');
		$this->redirect('Market:');
	}

	/**
	 * Aceppts the given offer
	 * @param int
	 * @return void
	 */
	public function renderAcceptOffer ($offerId)
	{
		try{
			$time = array(10, 10);
			$this->getOfferService()->accept($this->getOfferRepository()->findOneById($offerId), $this->getPlayerClan(), $time);
			$this->flashMessage('Koupeno!');
		}
		catch(InsufficientResourcesException $e){
			$this->flashMessage('Nedostatek surovin');
		}

		$this->redirect('Market:');
	}





}
