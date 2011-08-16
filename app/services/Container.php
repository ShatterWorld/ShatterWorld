<?php
class Container extends Nette\DI\Container {
	protected function createServiceEntityManager ()
	{
		$config = new Doctrine\ORM\Configuration;
		$driver = $config->newDefaultAnnotationDriver($this->params['doctrine']['entityPath']);
		$cache = $this->getService('doctrineCache');
		$config->setMetadataDriverImpl($driver);
		$config->setMetadataCacheImpl($cache);
		$config->setQueryCacheImpl($cache);
		$config->setProxyNamespace('Proxies');
		$config->setProxyDir($this->params['doctrine']['proxyDir']);
		$config->setAutoGenerateProxyClasses($this->params['productionMode']);
		$connection = $this->params['database'];
		return Doctrine\ORM\EntityManager::create($connection, $config);
	}
}
