<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use InsufficientResourcesException;
use Graph;
use Pager;

/**
 * A MarketPresenter
 * @author Petr Bělohlávek
 */
class MarketPresenter extends BasePresenter {

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

			if($this->getPlayerClan()->alliance){
				$form->addCheckbox('allianceOnly', 'Pouze alianční');
			}



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
			if($this->getPlayerClan()->alliance && $data['allianceOnly']){
				$data['alliance'] = $this->getPlayerClan()->alliance;
			}
			else{
				$data['alliance'] = null;
			}

			$this->getOfferService()->create($data);
			$this->flashMessage('Nabídnuto');
		}
		catch(InsufficientResourcesException $e){
			$this->flashMessage('Nedostatek surovin', 'error');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		}

		$this->redirect('Market:');
	}


	/**
	 * Displays default overview
	 * @return void
	 */
	public function renderDefault ()
	{
		$clan = $this->getPlayerClan();
		$stats = $this->context->stats->trading;

		$this->template->merchantSpeed = $stats->getMerchantSpeed($clan);
		$this->template->tradingRadius = $stats->getRadius($clan);
		$this->template->clanProfit = $stats->getProfit($clan);
		$this->template->clanOffers = $this->getClanOffers();
		$this->template->runningShipments = $this->getShipmentRepository()->getRunningShipments($this->getPlayerClan());
	}

	/**
	 * Displays availible offers
	 * @param int
	 * @return void
	 */
	public function renderBuy ($page = 1)
	{

		$offers = $this->getOfferRepository()->findReachable($this->getPlayerClan());
		$hasEnoughRes = array();
		$profits = array();

		$clan = $this->getPlayerClan();
		$clanHq = $clan->getHeadquarters();

		foreach ($offers as $key => $offer){
			$hasEnoughRes[$key] = $this->getResourceRepository()->checkResources($clan, array($offer->demand => $offer->demandAmount));
			$offerRepository = $this->getOfferRepository();
			$profits['short'][$key] = $offerRepository->getTotalMediatorProfit(Graph::SHORT, $clan, $offer);
			$profits['cheap'][$key] = $offerRepository->getTotalMediatorProfit(Graph::CHEAP, $clan, $offer);
		}

		$pageLength = $this->context->params['game']['setting']['pageLength']['market'];
		$this['pager']->setup(count($offers), $pageLength, $page);

		$this->template->offers = array_slice($offers, $pageLength*($page-1), $pageLength);
		$this->template->hasEnoughRes = $hasEnoughRes;
		$this->template->profits = $profits;
	}

	/**
	 * Signal deletes the given offer
	 * @param int
	 * @return void
	 */
	public function handleDeleteOffer ($offerId)
	{
		$this->getOfferService()->delete($this->getOfferRepository()->findOneBy(array(
			'id' => $offerId,
			'sold' => false
		)));
		$this->flashMessage('Staženo z nabídky');
		$this->redirect('Market:');
	}

	/**
	 * Signal that accepts the given offer by the shortest way
	 * @param int
	 * @return void
	 */
	public function handleAcceptOfferShort ($offerId)
	{
		try{
			$offerService = $this->getOfferService();
			$offerService->accept(Graph::SHORT, $this->getOfferRepository()->findOneById($offerId), $this->getPlayerClan());
			$this->flashMessage('Koupeno!');
		}
		catch(InsufficientResourcesException $e){
			$this->flashMessage('Nedostatek surovin');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		} catch (RuleViolationException $e){
			$this->flashMessage('Nemáte právo stahovat cizí nabídky', 'error');
		}
		$this->redirect('Market:');
	}

	/**
	 * Signal that accepts the given offer by the cheapest way
	 * @param int
	 * @return void
	 */
	public function handleAcceptOfferCheap ($offerId)
	{
		try{
			$offerService = $this->getOfferService();
			$offerService->accept(Graph::CHEAP, $this->getOfferRepository()->findOneById($offerId), $this->getPlayerClan());
			$this->flashMessage('Koupeno!');
		}
		catch(InsufficientResourcesException $e){
			$this->flashMessage('Nedostatek surovin');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		}

		$this->redirect('Market:');
	}

	/**
	 * Returns the short time of offer
	 * @param Entities\Offer
	 * @return int
	 */
	public function getShortTime ($offer)
	{
		$clan = $this->getPlayerClan();
		$d = $this->getClanRepository()->calculateTotalDistance($clan, $offer->owner, $this->getOfferRepository()->getMediators(Graph::SHORT, $clan, $offer));
		$t = floor($d * $this->context->stats->trading->getMerchantSpeed($offer->owner));
		return $t;
	}

	/**
	 * Returns the cheap time of offer
	 * @param Entities\Offer
	 * @return int
	 */
	public function getCheapTime ($offer)
	{
		$clan = $this->getPlayerClan();
		$d = $this->getClanRepository()->calculateTotalDistance($clan, $offer->owner, $this->getOfferRepository()->getMediators(Graph::CHEAP, $clan, $offer));
		$t = floor($d * $this->context->stats->trading->getMerchantSpeed($offer->owner));
		return $t;
	}


	protected function createComponentPager ()
	{
		return new Pager;
	}



}
