<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use InsufficientResourcesException;
use Graph;

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
		$this->template->clanOffers = $this->getClanOffers();
		$this->template->runningShipments = $this->getShipmentRepository()->getRunningShipments($this->getPlayerClan());
	}

	/**
	 * Displays availible offers
	 * @return void
	 */
	public function renderBuy ()
	{
		$offers = $this->getOfferRepository()->findReachable($this->getPlayerClan());
		$timeShort = array();
		$timeCheap = array();
		$hasEnoughRes = array();
		$profits = array();

		$clan = $this->getPlayerClan();
		$clanHq = $clan->getHeadquarters();

		foreach ($offers as $key => $offer){
			$hasEnoughRes[$key] = $this->getResourceRepository()->checkResources($clan, array($offer->demand => $offer->demandAmount));
			$offerRepository = $this->getOfferRepository();
			$profits['short'][$key] = $offerRepository->getTotalMediatorProfit(Graph::SHORT, $clan, $offer);
			$profits['cheap'][$key] = $offerRepository->getTotalMediatorProfit(Graph::CHEAP, $clan, $offer);

			$d = $this->getClanRepository()->calculateTotalDistance($clan, $offer->owner, $offerRepository->getMediators(Graph::SHORT, $clan, $offer));
			$t = floor($d / $this->context->stats->getMerchantSpeed($offer->owner));
			$timeShort[$key] = date("H:i:s", $t);

			$d = $this->getClanRepository()->calculateTotalDistance($clan, $offer->owner, $offerRepository->getMediators(Graph::CHEAP, $clan, $offer));
			$t = floor($d / $this->context->stats->getMerchantSpeed($offer->owner));
			$timeCheap[$key] = date("H:i:s", $t);

		}

		$this->template->offers = $offers;
		$this->template->timeShort = $timeShort;
		$this->template->timeCheap = $timeCheap;
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
		$this->getOfferService()->delete($this->getOfferRepository()->findOneById($offerId));
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





}
