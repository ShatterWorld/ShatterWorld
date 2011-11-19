<?php
namespace GameModule;
use Nette;
use Nette\Utils\Strings;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use InsufficientResourcesException;
use MissingDependencyException;
use ConflictException;


/**
 * A message presenter
 * @author Petr Bělohlávek
 */
class ResearchPresenter extends BasePresenter {


	/**
	 * Displays researches the clan has already researched
	 * @return void
	 */
	public function renderDefault ()
	{
		$clan = $this->getPlayerClan();
		$this->template->researchEfficiency = $this->getContext()->stats->getResearchEfficiency($clan);
		$this->template->researched = $this->getResearchRepository()->getResearched($clan);
		$this->template->all = $this->context->rules->getAll('research');
		$this->template->running = $this->getConstructionRepository()->getRunningResearches($clan);

	}

	/**
	 * Displays that can be researched
	 * @return void
	 */
	public function renderResearch ()
	{
		$clan = $this->getPlayerClan();
		$this->template->researched = $this->getResearchRepository()->getResearched($clan);
		$this->template->researchEfficiency = $this->getContext()->stats->getResearchEfficiency($clan);
		$this->template->all = $this->context->rules->getAll('research');
		$this->template->running = $this->getConstructionRepository()->getRunningResearches($clan);

	}

	/**
	 * Does the research
	 * @param string
	 * @return void
	 */
	public function handleResearch ($type)
	{
		try {
			$this->context->model->getConstructionService()->startResearch($this->getPlayerClan(), $type);
			$this->invalidateControl('orders');
			$this->invalidateControl('resources');
			$this->flashMessage('Výzkum zahájen');
		} catch (MultipleConstructionsException $e) {
			$this->flashMessage('Tento výzkum již zkoumáte', 'error');
		} catch (RuleViolationException $e) {
			$this->flashMessage('Tento výzkum nemůžete zkoumat', 'error');
		} catch (InsufficientResourcesException $e) {
			$this->flashMessage('Nemáte dostatek surovin', 'error');
		} catch (InsufficientOrdersException $e){
			$this->flashMessage('Nemáte dostatek rozkazů', 'error');
		} catch (MissingDependencyException $e){
			$this->flashMessage('Nemáte některé vyžadované výzkumy', 'error');
		} catch (ConflictException $e){
			$this->flashMessage('Konflikt výzkumů', 'error');
		}

		$this->redirect('this');
	}


}
