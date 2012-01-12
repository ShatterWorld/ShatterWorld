<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Exception;
use NotEnoughSpaceException;

/**
 * Clan Presenter
 * @author Petr Bělohlávek
 */
class ClanPresenter extends BasePresenter {

	/**
	 * Show players clan
	 * @return void
	 */
	public function actionDefault ()
	{
		$this->redirect('Clan:show', $this->getPlayerClan()->id);
	}

	/**
	 * Show action
	 * @param int
	 * @return void
	 */
	public function actionShow ($clanId = null)
	{
		if ($this->getPlayerClan()) {
			if ($clanId === null) {
				$this->redirect('Clan:show', $this->getPlayerClan()->id);
			}
		} else {
			$this->redirect('Clan:new');
		}
	}

	/**
	 * Show render
	 * @param int
	 * @return void
	 */
	public function renderShow ($clanId)
	{
		if ($clan = $this->context->model->getClanRepository()->findOneById($clanId)) {
			$playerClan = $this->getPlayerClan();

			if (($clan->id === $playerClan->id || $clan->alliance && $playerClan->alliance && $clan->alliance->id === $playerClan->alliance->id)) {
				$this->template->scoreRules = $this->context->rules->getAll('score');
				$this->template->resourceRules = $this->context->rules->getAll('resource');
				$this->template->facilityRules = $this->context->rules->getAll('facility');
				$facilities = $this->context->model->getFacilityRepository()->getClanFacilities($clan);
				$facilitiesData = array();
				foreach ($facilities as $name => $level){
					$production = $this->context->rules->get('facility', $name)->getProduction();
					$facility = array();
					$facility['name'] = $name;
					$facility['level'] = $level;
					$facility['production'] = $production;
					$facilitiesData[] = $facility;
				}

				$this->template->facilities = $facilitiesData;
				$this->template->clanScores = $this->context->model->getScoreRepository()->getClanScoreArray($clan);
			}

			$this->template->clan = $clan;
			$this->template->totalClanScore = $this->context->model->getScoreRepository()->getTotalClanScore($this->getPlayerClan());
			$this->template->playerClan = $playerClan;
			$this->template->clanQuota = $this->context->params['game']['stats']['clanQuota'];
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
		if ($this->getPlayerClan() === null || count($this->getPlayerClan()->user->clans) < $this->context->params['game']['stats']['clanQuota']) {
			try{
				$data = $form->getValues();
				$data['user'] = $this->getUserRepository()->find($this->getUser()->getId());
				$this->getService('clanService')->create($data);
				$this->flashMessage(sprintf('Klan %s byl založen.', $data['name']));
			} catch (NotEnoughSpaceException $e) {
				$this->flashMessage('Klan se nepovedlo vytvořit, mapa je už plná', 'error');
				$this->redirect('Clan:');
			}
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
