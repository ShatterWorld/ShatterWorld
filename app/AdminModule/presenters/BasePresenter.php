<?php
namespace AdminModule;
use Nette;

abstract class BasePresenter extends \BasePresenter
{
	public function startup ()
	{
		parent::startup();
		if (!$this->getUser()->isAllowed('administration')) {
			$this->redirect(':Front:Dashboard:');
		}
	}
}
