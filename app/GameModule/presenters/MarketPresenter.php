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
	* Redirects if user has no clan, otherwise displays his clan
	* @return void
	*/
	public function renderDefault ()
	{
		$this->template->offers = $this->getClanOffers();
	}

	/**
	* Action for new clan
	* @return void
	*/
	public function actionSell ()
	{
		$this->template->offers = $this->getClanOffers();
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





}
