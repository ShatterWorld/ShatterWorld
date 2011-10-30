<?php
namespace GameModule;
use Grid;
use Nette;

class ReportPresenter extends BasePresenter
{
	public function renderDefault ()
	{
		$this->template->reports = $this->getReportRepository()->findByOwner($this->getPlayerClan());
		$this->template->rules = $this->context->rules;
		$this->getReportService()->markReadAll($this->getPlayerClan());
	}

	protected function createComponentUnitGrid ()
	{
		return new Grid($this, 'unitGrid', $this->context->rules->getDescriptions('unit'));
	}

	protected function createComponentResourceGrid ()
	{
		return new Grid($this, 'resourceGrid', $this->context->rules->getDescriptions('resource'));
	}
}
