<?php
namespace Services;
use Entities;
use Nette\Diagnostics\Debugger;

class User extends BaseService
{
	public function create ($values, $flush = TRUE)
	{
		$user = parent::create($values, $flush);
		$this->context->model->getProfileService()->create(array('user' => $user));
		return $user;
	}
}
