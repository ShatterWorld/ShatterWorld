<?php
namespace GameModule;
use Nette;
use Nette\Utils\Strings;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use InsufficientResourcesException;


/**
 * A message presenter
 * @author Petr Bělohlávek
 */
class ResearchPresenter extends BasePresenter {


	/**
	 * Displays research
	 * @return void
	 */
	public function renderDefault ()
	{
		$this->template->researched = $this->getResearchRepository()->getResearched($this->getPlayerClan());
		$this->template->all = $this->context->rules->getAll('research');
	}


}
