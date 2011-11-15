<?php
namespace GameModule;
use Nette;
use Nette\Utils\Strings;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use InsufficientResourcesException;
use MissingDependencyException;


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
		$this->template->researched = $this->getResearchRepository()->getResearched($this->getPlayerClan());
		$this->template->all = $this->context->rules->getAll('research');
	}

	/**
	 * Displays researches
	 * @return void
	 */
	public function renderResearch ()
	{
		Debugger::barDump($this->getConstructionRepository()->getRunningResearches($this->getPlayerClan()));
		$all = $this->context->rules->getAll('research');

		$researchTimes = array();
		foreach($all as $key => $res){
			$t = $res->getResearchTime();

			$h = floor($t / 3600);
			$t -= $h*3600;
			$m = floor($t / 60);
			$t -= $m*60;
			$s = $t;

			if ($s < 10){
				$s = '0'.$s;
			}
			if ($m < 10){
				$m = '0'.$m;
			}

			$researchTimes[$key] = $h.':'.$m.':'.$s;
		}

		$this->template->researched = $this->getResearchRepository()->getResearched($this->getPlayerClan());
		$this->template->researchTimes = $researchTimes;
		$this->template->all = $all;
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
		}

		$this->redirect('this');
	}


}
