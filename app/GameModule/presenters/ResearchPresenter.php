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
		try{
			$this->getResearchService()->research($type, $this->getPlayerClan());
			$this->flashMessage('Výzkum zahájen');
		}
		catch(InsufficientResourcesException $e){
			$this->flashMessage('Nedostatek surovin', 'error');
		}
		catch(MissingDependencyException $e){
			$this->flashMessage('Chybí závislost', 'error');
		}

		$this->redirect('this');
	}

	/**
	 * Upgrades the already existing research
	 * @param int
	 * @return void
	 */
	public function handleUpgrade ($resId)
	{
		$research = $this->getResearchRepository()->find($resId);
		try{
			$this->getResearchService()->upgrade($research, $this->getPlayerClan());
			$this->flashMessage('Vylepšení zahájeno');
		}
		catch(InsufficientResourcesException $e){
			$this->flashMessage('Nedostatek surovin', 'error');
		}

		$this->redirect('this');
	}

}
