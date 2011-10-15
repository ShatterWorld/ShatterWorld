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
		$this->template->alliance = $alliance = $this->getPlayerClan()->getAlliance();
		$this->template->applicants = $this->getClanRepository()->findBy(array('allianceApplication' => $alliance->id, 'alliance' => NULL));
	}
	
	public function renderNeutral ()
	{
		$this->template->clan = $this->getPlayerClan();
		$this->template->visibleAlliances = $this->getAllianceRepository()->getVisibleAlliances($this->getPlayerClan());
	}
	
	public function handleApply ($id)
	{
		$clan = $this->getPlayerClan();
		if ($clan->alliance === NULL) {
			$this->getClanService()->update($clan, array('allianceApplication' => $this->getAllianceRepository()->find($id)));
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
}