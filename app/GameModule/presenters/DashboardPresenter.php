<?php
namespace GameModule;
use Nette;

class DashboardPresenter extends BasePresenter
{
	public function startup ()
	{
		parent::startup();
		if (!$this->getClanRepository()->findOneByUser($this->getUser()->getId())) {
			$this->redirect('Clan:new');
		}
	}
}
