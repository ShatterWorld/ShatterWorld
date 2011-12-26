<?php
namespace GameModule;
use Nette;
use Nette\Diagnostics\Debugger;


/**
 * Scoreboard Presenter
 * @author Petr Bělohlávek
 */
class ScoreboardPresenter extends BasePresenter {

	/**
	* Show scoreboard of clans
	* @return void
	*/
	public function renderDefault ()
	{
		$this->template->chart = $this->context->model->getScoreRepository()->getClanChart();
	}

	/**
	* Show scoreboard of alliances
	* @return void
	*/
	public function renderAlliance ()
	{
		$this->template->chart = $this->context->model->getScoreRepository()->getAllianceChart();
	}



}
