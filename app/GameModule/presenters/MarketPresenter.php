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
		$offers = $this->getOfferRepository()->findAll();


//need field. not proxy
/*
		foreach ($offers as $offer){
			$ownerFields = $this->getFieldRepository()->findByOwner($offer->owner->id);
			$targetField;
			foreach($ownerFields as $field) {
				if($field->facility === 'headquarters'){
					$targetField = $field;
					break;
				}
			}
			//Debugger::barDump($ownerFields[0]->facility);
			Debugger::barDump($targetField);

			$clanFields = $this->getFieldRepository()->findByOwner($this->getPlayerClan()->id);
			$targetField2;
			foreach($clanFields as $field) {
				if($field->facility === 'headquarters'){
					$targetField2 = $field;
					break;
				}
			}



			//$offer['time'] = $this->getFieldRepository()->calculateDistance($targetField, $targetField2);
		}*/

		$this->template->offers = $offers;
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
		$this->getOfferService()->accept($this->getOfferRepository()->findOneById($offerId));
		$this->flashMessage('Koupeno!');
		$this->redirect('Market:Buy');
	}





}
