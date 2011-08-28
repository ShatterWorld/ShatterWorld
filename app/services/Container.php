<?php
class Container extends Nette\DI\Container 
{	
	public function createEntityManager ()
	{
		$config = new Doctrine\ORM\Configuration;
		$driver = $config->newDefaultAnnotationDriver($this->params['doctrine']['entityDir']);
		$cache = $this->getService('doctrineCache');
		$config->setMetadataDriverImpl($driver);
		$config->setMetadataCacheImpl($cache);
		$config->setQueryCacheImpl($cache);
		$config->setProxyNamespace($this->params['doctrine']['proxyNamespace']);
		$config->setProxyDir($this->params['doctrine']['proxyDir']);
		$config->setAutoGenerateProxyClasses(!$this->params['productionMode']);
		if (!$this->params['productionMode']) {
			$config->setSQLLogger(\Nella\Doctrine\Panel::register());
		}
		$connection = $this->params['database'];
		return EntityManager::create($connection, $config);
	}
	
	protected function createServiceAuthorizator ()
	{
		$acl = new Nette\Security\Permission;
		$acl->addResource('administration');
		$acl->addRole('player');
		$acl->addRole('admin');
		$acl->allow('admin');
		return $acl;
	}
	
	protected function createServiceModel ()
	{
		$context = new static;
		$context->lazyCopy($this, 'entityManager');
		$context->lazyCopy($this, 'cacheStorage');
		$context->lazyCopy($this, 'doctrineCache');
		$context->lazyCopy($this, 'rules');
		$context->params['game'] = &$this->params['game'];
		$context->params['database'] = &$this->params['database'];
		$context->params['doctrine'] = &$this->params['doctrine'];
		$context->params['appDir'] = $this->params['appDir'];
		$context->params['wwwDir'] = $this->params['wwwDir'];
		$context->params['productionMode'] = $this->params['productionMode'];
		return new ModelContainer($context);
	}
	
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
