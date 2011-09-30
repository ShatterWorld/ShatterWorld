<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;

class UnitPresenter extends BasePresenter
{

	protected function createComponentTrainUnitForm ()
	{

		$form = new Form;

		$units = $this->context->rules->getAll('unit');

		foreach ($units as $name => $unit){
			$form->addText($name, $name . ' (' . $unit->getAttack() . '/' . $unit->getDefense() . ')');

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
