<?php
namespace GameModule;
use Nette;

class ReportPresenter extends BasePresenter
{
	public function renderDefault ()
	{
		$this->template->reports = $this->getReportRepository()->findByOwner($this->getPlayerClan());
	}
}