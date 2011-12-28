<?php
namespace GameModule;
use Nette;
use Nette\Diagnostics\Debugger;
use Pager;


/**
 * Scoreboard Presenter
 * @author Petr Bělohlávek
 */
class ScoreboardPresenter extends BasePresenter {

	/**
	* Show scoreboard of clans
	* @param int
	* @return void
	*/
	public function renderDefault ($page = 1)
	{
		$pageLength = $this->context->params['game']['setting']['pageLength']['scoreboard'];
		$this['pager']->setup($this->context->model->getClanRepository()->getClanCount(), $pageLength, $page);

		$this->template->chart = $this->getScoreRepository()->getClanPage($pageLength, $page);
		$this->template->offset = ($page-1)*$pageLength;
	}

	/**
	* Show scoreboard of alliances
	* @param int
	* @return void
	*/
	public function renderAlliance ($page = 1)
	{
		$pageLength = $this->context->params['game']['setting']['pageLength']['scoreboard'];
		$this['pager']->setup($this->context->model->getAllianceRepository()->getAllianceCount(), $pageLength, $page);

		$this->template->chart = $this->getScoreRepository()->getAlliancePage($pageLength, $page);
		$this->template->offset = ($page-1)*$pageLength;
	}

	protected function createComponentPager ()
	{
		return new Pager;
	}


}
