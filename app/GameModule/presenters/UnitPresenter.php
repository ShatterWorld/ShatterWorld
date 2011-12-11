<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use InsufficientCapacityException;
use InsufficientResourcesException;
use Rules\Facilities\IConstructionFacility;

class UnitPresenter extends BasePresenter
{
	public function renderDefault ()
	{
		$this->template->registerHelper('getDescription', callback($this, 'getUnitDescription'));
		$this->template->registerHelper('getUnitAttack', callback($this, 'getUnitAttack'));
		$this->template->registerHelper('getUnitDefense', callback($this, 'getUnitDefense'));
		$this->template->units = $this->getClanUnits();
	}

	public function renderTrain ()
	{
		$slots = array();
		$totalSlots = $this->context->stats->units->getSlots($this->getPlayerClan());
		foreach ($this->context->rules->getAll('facility') as $name => $rule) {
			if ($rule instanceof IConstructionFacility) {
				$slots[$name] = $rule;
				if(!isset($totalSlots[$name])){
					$totalSlots[$name] = 0;
				}
			}
		}

		$resources = array();
		foreach ($this->context->rules->getAll('resource') as $name => $rule) {
			$resources[$name] = $rule;
		}

		$this->template->registerHelper('getUnitAttack', callback($this, 'getUnitAttack'));
		$this->template->registerHelper('getUnitDefense', callback($this, 'getUnitDefense'));
		$this->template->resourceRules = $resources;
		$this->template->unitRules = $this->context->rules->getAll('unit');
		$this->template->slots = $slots;
		$this->template->totalSlots = $totalSlots;
	}

	protected function createComponentTrainUnitForm ()
	{

		$form = new Form;

		$units = $this->context->rules->getAll('unit');

		foreach ($units as $name => $unit){
			$form->addText($name, $unit->getDescription())
				->addCondition(Form::FILLED)
				->addRule(Form::INTEGER, 'Počet jednotek musí být celé číslo')
				->addRule(Form::RANGE, 'Počet jednotek musí být kladné číslo', array(0, NULL));
		}

		$form->addSubmit('send', 'Trénovat');

		//$form->setTranslator($translator);


		$form->onSuccess[] = callback($this, 'submitTrainUnitForm');

		return $form;
	}

	public function submitTrainUnitForm ($form)
	{

		$data = $form->getValues();

		$service = $this->getService('constructionService');
		$clan = $this->getPlayerClan();

		try{
			$service->startUnitTraining($clan, $data);
			$this->flashMessage("Trénování zahájeno");
		} catch (InsufficientResourcesException $e){
			$this->flashMessage("Nedostatek surovin", 'error');

		} catch (InsufficientCapacityException $e){
			$this->flashMessage("Nedostatek slotů", 'error');

		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		}

		$this->redirect('Unit:Train');
	}

	public function getUnitDescription ($type)
	{
		return $this->context->rules->get('unit', $type)->getDescription();
	}

	public function getUnitAttack ($unitName)
	{
		return $this->context->stats->units->getUnitAttack($this->getPlayerClan(), $unitName);
	}

	public function getUnitDefense ($unitName)
	{
		return $this->context->stats->units->getUnitDefense($this->getPlayerClan(), $unitName);
	}
}
