<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use InsufficientCapacityException;
use InsufficientResourcesException;

class UnitPresenter extends BasePresenter
{


	public function renderDefault ()
	{
		$this->template->units = $this->getClanUnits();
	}

	public function actionTrain ()
	{
		$this->template->units = $this->getClanUnits();
//		Debugger::barDump($this->template->units);
	}


	protected function createComponentTrainUnitForm ()
	{

		$form = new Form;

		$units = $this->context->rules->getAll('unit');

		foreach ($units as $name => $unit){
			$form->addText($name, $name . ' (' . $unit->getAttack() . '/' . $unit->getDefense() . ')')
				->addRule(Form::INTEGER, 'Počet jednotek musí být celé číslo')
				->addRule(Form::RANGE, 'Počet jednotek musí být kladné číslo', array(0, 999999999));
		}

		$form->addSubmit('send', 'Trénovat');

		//$form->setTranslator($translator);


		$form->onSuccess[] = callback($this, 'submitTrainUnitForm');

		return $form;
	}

	public function submitTrainUnitForm ($form)
	{

		$data = $form->getValues();
		//Debugger::barDump($data);

		$service = $this->getService('constructionService');
		$clan = $this->getPlayerClan();

		try{
			$service->startUnitTraining($clan, $data);
			$this->flashMessage("Trénování zahájeno");
		}
		catch (InsufficientResourcesException $e){
			$this->flashMessage("Nedostatek surovin", 'error');

		}
		catch (InsufficientCapacityException $e){
			$this->flashMessage("Nedostatek slotů", 'error');

		}



		$this->redirect('Unit:');

	}


}
