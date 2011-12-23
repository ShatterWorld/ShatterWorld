<?php
namespace GameModule;
use Nette;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;


/**
 * Score Presenter
 * @author Petr Bělohlávek
 */
class ScorePresenter extends BasePresenter {

	public function renderDefault ()
	{
		$this->template->clanScore = $this->context->stats->score->getClanScore($this->getPlayerClan());
		$this->template->totalClanScore = $this->context->stats->score->getTotalClanScore($this->getPlayerClan());
	}


}
