<?php
use Nette\Utils\Strings;

class ModelContainer extends Nette\Object
{
	/** @var array */
	protected $services = array();
	
	/** @var Nette\DI\Container */
	protected $context;
	
	public function __construct ($context)
	{
		$this->context = $context;
	}
	
	public function getService ($name)
	{
		$namespace = $this->context->params['doctrine']['entityNamespace'];
		if (!Nette\Utils\Strings::startsWith($name, $namespace)) {
			$entityName = $namespace . '\\' . $name;
		}
		else {
			$entityName = $name;
		}
		if (!isset($this->services[$name])) {
			if (!class_exists($class = $this->context->params['doctrine']['serviceNamespace'] . '\\' . $name)) {
				$class = 'Services\\BaseService';
			}
			$this->services[$name] = new $class($this->context, $entityName);
		}
		return $this->services[$name];
	}
	
	public function &__get ($name)
	{
		if (Strings::endsWith($name, 'Service')) {
			return $this->getService(substr($name, 0, -7));
		}
		if (Strings::endsWith($name, 'Repository')) {
			return $this->getService(substr($name, 0, -10))->getRepository();
		}
		return parent::__get($name);
	}
	
	public function __call ($name, $args)
	{
		if (Strings::startsWith($name, 'get')) {
			if (Strings::endsWith($name, 'Service')) {
				return $this->getService(substr($name, 3, -7));
			}
			if (Strings::endsWith($name, 'Repository')) {
				return $this->getService(substr($name, 3, -10))->getRepository();
			}
		}
		return parent::__call($name, $args);
	}
}