<?php
namespace GameModule;
use Grid;
use Pager;
use Nette;

class ReportPresenter extends BasePresenter
{
	public function renderDefault ($page = 1)
	{
		$pageLength = 10;
		$this['pager']->setup($this->getReportRepository()->getCount($this->getPlayerClan()), $pageLength, $page);
		$this->template->reports = $this->getReportRepository()->getPage($this->getPlayerClan(), $pageLength, $page);
		$this->template->rules = $this->context->rules;
	}

	public function handleMarkRead ()
	{
		$this->getReportService()->markReadAll($this->getPlayerClan());
		$this->redirect('this');
	}

	protected function createComponentUnitGrid ()
	{
		return new Grid($this, 'unitGrid', $this->context->rules->getDescriptions('unit'));
	}

	protected function createComponentResourceGrid ()
	{
		return new Grid($this, 'resourceGrid', $this->context->rules->getDescriptions('resource'));
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
