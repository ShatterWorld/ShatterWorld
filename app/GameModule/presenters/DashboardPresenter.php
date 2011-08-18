<?php
namespace GameModule;
use Nette;

class DashboardPresenter extends BasePresenter
{
	public function startup ()
	{
		parent::startup();
		if (!$this->getService('clanService')->getRepository()->find($this->getUser()->getId())) {
			$this->redirect('Clan:new');
		}
	}
}
