<?php
namespace GameModule;
use Grid;
use Pager;
use Nette;
use Nette\Diagnostics\Debugger;

class ReportPresenter extends BasePresenter
{
	public function renderDefault ($page = 1)
	{
		$pageLength = $this->context->params['game']['setting']['pageLength']['report'];
		$this['pager']->setup($this->getReportRepository()->getCount($this->getPlayerClan()), $pageLength, $page);
		$this->template->reports = $this->getReportRepository()->getPage($this->getPlayerClan(), $pageLength, $page);
		$this->template->rules = $this->context->rules;
		$this->template->dateFormat = $this->context->params['game']['setting']['dateFormat']['report'];
	}

	public function handleMarkReadAll ()
	{
		$this->getReportService()->markReadAll($this->getPlayerClan());
		$this->redirect('this');
	}

	public function handleMarkRead ($reportId)
	{
		$this->getReportService()->markRead($this->getPlayerClan(), $reportId);
		Debugger::fireLog($a = 'a');
	}

	protected function createComponentUnitGrid ()
	{
		return new Grid($this, 'unitGrid', $this->context->rules->getDescriptions('unit'));
	}

	protected function createComponentResourceGrid ()
	{
		return new Grid($this, 'resourceGrid', $this->context->rules->getAll('resource'));
	}

	protected function createComponentResearchGrid ()
	{
		return new Grid($this, 'researchGrid', $this->context->rules->getDescriptions('research'));
	}

	protected function createComponentPager ()
	{
		return new Pager;
	}
}
