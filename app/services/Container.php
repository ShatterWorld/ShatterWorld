<?php
class Container extends Nette\DI\Container 
{
	public function lazyCopy ($container, $name)
	{
		$this->addService($name, function () use ($container, $name) {
			return $container->getService($name);
		});
	}
	
	public function getService ($name) 
	{
		if (Nette\Utils\Strings::endsWith($name, 'Service')) {
			return $this->model->getService(ucfirst(substr($name, 0, -7)));
		}
		return parent::getService($name);
	}
	
	public function __get ($name)
	{
		return $this->getService($name);
	}
}
