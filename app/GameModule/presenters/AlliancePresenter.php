<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;

class AlliancePresenter extends BasePresenter
{
	public function actionDefault ()
	{
		if (!$this->getPlayerClan()->getAlliance()) {
			$this->setView('neutral');
		}
	}
	
	public function renderDefault ()
	{
		$this->template->alliance = $this->getPlayerClan()->getAlliance();
	}
	
	protected function createComponentNewAllianceForm ()
	{
		$form = new Form();
		$form->addText('name', 'Jméno')
			->setRequired('Zadejte jméno aliance.');
		$form->addSubmit('send', 'Založit alianci');
		$form->onSuccess[] = callback($this, 'submitNewAllianceForm');
		return $form;
	}
	
	public function submitNewAllianceForm (Form $form)
	{
		$values = $form->getValues();
		$values['leader'] = $this->getPlayerClan();
		$this->context->model->getAllianceService()->create($values);
		$this->flashMessage(sprintf('Aliance %s byla založena', $values['name']));
		$this->redirect('default');
	}
}