<?php
class Container extends Nette\DI\Container {
	
	/** @var array */
	protected $modelServices = array();
	
	protected function createServiceEntityManager ()
	{
		$config = new Doctrine\ORM\Configuration;
		$driver = $config->newDefaultAnnotationDriver($this->params['doctrine']['entityDir']);
		$cache = $this->getService('doctrineCache');
		$config->setMetadataDriverImpl($driver);
		$config->setMetadataCacheImpl($cache);
		$config->setQueryCacheImpl($cache);
		$config->setProxyNamespace($this->params['doctrine']['proxyNamespace']);
		$config->setProxyDir($this->params['doctrine']['proxyDir']);
		$config->setAutoGenerateProxyClasses($this->params['productionMode']);
		$connection = $this->params['database'];
		return Doctrine\ORM\EntityManager::create($connection, $config);
	}
	
	public function getModelService ($name)
	{
		$namespace = $this->params['doctrine']['entityNamespace'];
		if (!Nette\Utils\Strings::startsWith($name, $namespace)) {
			$entityName = $namespace . '\\' . $name;
		}
		else {
			$entityName = $name;
		}
		if (!$this->hasService($name)) {
			$class = 'Services\\BaseService';
			$this->addService($name, new $class($this->getService('entityManager'), $entityName));
		}
		return $this->getService($name);
	}
	
	public function getService ($name) 
	{
		if (Nette\Utils\Strings::endsWith($name, 'Service')) {
			return $this->getModelService(ucfirst(substr($name, 0, strpos($name, 'Service'))));
		}
		return parent::getService($name);
	}
	
	public function __get ($name)
	{
		return $this->getService($name);
	}
}
