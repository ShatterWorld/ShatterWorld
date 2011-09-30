<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;
class UnitPresenter extends BasePresenter
{

	protected function createComponentTrainUnitForm ()
	{

		$form = new Form;

		$units = $this->context->rules->getAll('unit');

		foreach ($units as $name => $unit){
			$form->addText($name, $name);

		}

		$form->addSubmit('send', 'Trénovat');

		//$form->setTranslator($translator);


		$form->onSuccess[] = callback($this, 'submitTrainUnitForm');

		return $form;
	}

	public function submitTrainUnitForm ($form)
	{
		/*$data = $form->getValues();
		$data['role'] = 'player';
		$this->getService('userService')->create($data);

		$this->flashMessage("Vaše registrace proběhla úspěšně");

		$this->redirect('Homepage:');*/

	}


}
