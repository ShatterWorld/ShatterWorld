<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;


/**
 * A ClanPresenter
 * @author Petr Bělohlávek
 */
class ClanPresenter extends BasePresenter {
	
	/**
	* Redirects if user has no clan, otherwise displays his clan
	* @return void
	*/
	public function renderDefault ()
	{
		if (!$this->getPlayerClan()) {
			$this->redirect('Clan:new');
		}	
		$this->template->clan = $this->getPlayerClan();
	}
	
	/**
	* Action for new clan
	* @return void
	*/
	public function actionNew ()
	{
		if ($this->getPlayerClan()) {
			$this->redirect('Clan:');
		}
	}
	
	/**
	* Creates the New form
	* @return Nette\Application\UI\Form
	*/
	protected function createComponentNewClanForm () 
	{
		$form = new Form();
		$form->addText('name', 'Jméno klanu')
			->setRequired('Vyplňte, prosím, jméno klanu.');
		$form->addSubmit('submit', 'Založit klan');
		$form->onSuccess[] = callback($this, 'submitNewClanForm');
		return $form;
	}
	
	/**
	* New clan slot
	* @param Nette\Application\UI\Form
	* @return void
	*/
	public function submitNewClanForm (Form $form)
	{
		if (!$this->getPlayerClan()) {
			$data = $form->getValues();
			$data['user'] = $this->getUserRepository()->find($this->getUser()->getId());
			$this->getService('clanService')->create($data);
			$this->flashMessage(sprintf('Klan %s byl založen.', $data['name']));
			
		}
		$this->redirect('Clan:');
	}

	/**
	* Creates the Delete form
	* @return Nette\Application\UI\Form
	*/
	protected function createComponentDeleteClanForm () 
	{
		$form = new Form();
		$form->addSubmit('submit', 'Smazat klan');
		$form->onSuccess[] = callback($this, 'submitDeleteClanForm');
		return $form;
	}
	
	/**
	* Delete clan slot
	* @param Nette\Application\UI\Form
	* @return void
	*/
	public function submitDeleteClanForm (Form $form)
	{
		if ($this->getPlayerClan()) {
			$this->getClanService()->delete($this->getPlayerClan());
			$this->flashMessage('Klan byl smazán.');
		}
		$this->redirect('Clan:new');
		
	}
	
	
}
