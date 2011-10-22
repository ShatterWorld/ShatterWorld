<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;



/**
 * A MarketPresenter
 * @author Petr Bělohlávek
 */
class MarketPresenter extends BasePresenter {

	/**
	* Action for sell
	* @return void
	*/
	public function actionSell ()
	{
		$this->template->offers = $this->getClanOffers();
	}

	/**
	* Action for buy
	* @return void
	*/
	public function actionBuy ()
	{
		$offers = $this->getOfferRepository()->findReachable($this->getPlayerClan(), 5);
		$clanHq = $this->getPlayerClan()->getHeadquarters();

		foreach ($offers as $key => $offer){
			$targetHq = $offer->owner->getHeadquarters();
			$time[$key] = $this->getFieldRepository()->calculateDistance($clanHq, $targetHq);
		}

		$this->template->offers = $offers;
		$this->template->time = $time;
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

		$data = $form->getValues();
		$data['owner'] = $this->getPlayerClan();
		Debugger::barDump($data);

		$this->getOfferService()->create($data);
		$this->flashMessage('Nabídnuto');
		$this->redirect('Market:Sell');
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
		$time = array(10, 5);
		$this->getOfferService()->accept($this->getOfferRepository()->findOneById($offerId), $this->getPlayerClan(), $time);
		$this->flashMessage('Koupeno!');
		$this->redirect('Market:Buy');
	}





}
