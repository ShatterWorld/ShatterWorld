<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;

class AlliancePresenter extends BasePresenter
{
	public function actionDefault ()
	{
		if (!$this->getPlayerClan()->getAlliance()) {
			$this->flashMessage('Nemáte ještě alianci', 'error');
			$this->redirect('Alliance:neutral');
		}
	}

	public function renderDefault ()
	{
		$this->template->alliance = $this->getPlayerClan()->getAlliance();
	}

	public function actionNew ()
	{
		if ($this->getPlayerClan()->getAlliance()) {
			$this->flashMessage('Už máte alianci', 'error');
			$this->redirect('Alliance:');
		}
	}

	public function actionNeutral ()
	{
		if ($this->getPlayerClan()->getAlliance()) {
			$this->flashMessage('Už máte alianci', 'error');
			$this->redirect('Alliance:');
		}
	}

	public function renderNeutral ()
	{
		$this->template->clan = $this->getPlayerClan();
		$this->template->visibleAlliances = $this->getAllianceRepository()->getVisibleAlliances($this->getPlayerClan());
	}

	public function actionLeave ()
	{
		if (!$this->getPlayerClan()->getAlliance()) {
			$this->redirect('Alliance:');
		}
	}

	public function handleApply ($id)
	{
		$clan = $this->getPlayerClan();
		//Debugger::barDump($this->getAllianceRepository()->find($id));
		//throw new Exception();
		if ($clan->alliance === NULL) {
			$clan->addApplication($this->getAllianceRepository()->find($id));
		}
		$this->redirect('this');
	}

	public function handleAccept ($id)
	{
		$this->getAllianceService()->addMember($this->getPlayerClan()->alliance, $this->getClanRepository()->find($id));
		$this->redirect('this');
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

	protected function createComponentLeaveAllianceForm ()
	{
		$form = new Form();
		$form->addSubmit('send', 'Opustit alianci');
		$form->onSuccess[] = callback($this, 'submitLeaveAllianceForm');
		return $form;
	}

	public function submitLeaveAllianceForm (Form $form)
	{
		try{
			$clan = $this->getPlayerClan();
			$this->context->model->getAllianceService()->leaveAlliance($clan->getAlliance(), $clan);
			$this->flashMessage('Váš klan opustil alianci');
			$this->redirect('Alliance:neutral');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Nemůžete opustit alianci ve které nejste členem', 'error');
			$this->redirect('Alliance');
		}
	}
}
