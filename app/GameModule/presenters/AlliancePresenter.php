<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Exception;
use RuleViolationException;

class AlliancePresenter extends BasePresenter
{
	/**
	* Show players clan
	* @return void
	*/
	public function actionDefault ()
	{
		if($this->getClanAlliance()){
			$this->redirect('Alliance:show', $this->getClanAlliance()->id);
		}
		else{
			$this->redirect('Alliance:neutral');
		}
	}

	/**
	* Show action
	* @param int
	* @return void
	*/
	public function actionShow ($allianceId = null)
	{
		if ($allianceId === null){
			$this->redirect('Alliance:show', $this->getClanAlliance()->id);
		}
	}

	/**
	* Show render
	* @param int
	* @return void
	*/
	public function renderShow ($allianceId)
	{
		if ($alliance = $this->context->model->getAllianceRepository()->findOneById($allianceId)){
			$playerAlliance = $this->getClanAlliance();
			if ($alliance->id !== $playerAlliance->id){
				//if not yours alliance (hide sth)
			}

			$this->template->memberScores = $this->context->model->getScoreRepository()->getAllianceScoreArray($alliance);
			$this->template->totalAllianceScore = $this->context->model->getScoreRepository()->getTotalAllianceScore($alliance);
			$this->template->alliance = $alliance;
			$this->template->clan = $this->getPlayerClan();

		}
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

	public function actionManagement ()
	{
		if (!$this->isLeader()) {
			$this->flashMessage('Nemáte oprávnění spravovat alianci', 'error');
			$this->redirect('Alliance:');
		}
	}

	public function renderManagement ()
	{
		$this->template->alliance = $this->getPlayerClan()->getAlliance();
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
		if ($clan->alliance === NULL) {
			$this->getClanService()->addApplication($clan, $this->getAllianceRepository()->find($id));
			$this->flashMessage('Žádost odeslána');
		}
		else{
			$this->flashMessage('Již jste členem jiné aliance', 'error');
		}
		$this->redirect('this');
	}

	public function handleAccept ($id)
	{
		if (!$this->isLeader()) {
			$this->flashMessage('Nemáte oprávnění k takové akci', 'error');
			$this->redirect('Alliance:');
		}

		$this->getAllianceService()->addMember($this->getPlayerClan()->alliance, $this->getClanRepository()->find($id));
		$this->redirect('Alliance:');
	}

	public function handleDecline ($id)
	{
		if (!$this->isLeader()) {
			$this->flashMessage('Nemáte oprávnění k takové akci', 'error');
			$this->redirect('Alliance:');
		}

		$this->getAllianceService()->declineApplication($this->getPlayerClan()->alliance, $this->getClanRepository()->find($id));
		$this->flashMessage('Přihláška smazána');

		$this->redirect('this');
	}

	public function handleFire ($id)
	{
		$member = $this->getClanRepository()->find($id);
		if (!$this->isLeader()) {
			$this->flashMessage('Nemáte oprávnění propouštět členy', 'error');
			$this->redirect('Alliance:');
		}

		try{
			$this->getAllianceService()->fireMember($this->getPlayerClan()->alliance, $member);
			$this->flashMessage('Vyloučen');
		}
		catch (RuleViolationException $e){
			$this->flashMessage('K tomuto nemáte právo', 'error');
		}
		$this->redirect('this');
	}

	public function handleHandOverLeaderShip ($id)
	{
		try{
			$this->getAllianceService()->handOverLeadership($this->getPlayerClan()->alliance, $this->getClanRepository()->find($id));
			$this->flashMessage('Vůdcovství předáno');
		}
		catch (Exception $e){
			$this->flashMessage('Tomuto hráči nemůžete předat vůdcovství', 'error');
		}
		$this->redirect('Alliance:');
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
