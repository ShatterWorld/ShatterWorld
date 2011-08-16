<?php
class Container extends Nette\DI\Container {
	protected function createServiceEntityManager ($context)
	{
		$config = new Doctrine\ORM\Configuration;
		$driver = $config->newDefaultAnnotationDriver($context->params['doctrine']['entityPath']);
		$cache = $this->getService('doctrineCache');
		$config->setMetadataDriverImpl($driver);
		$config->setMetadataCacheImpl($cache);
		$config->setQueryCacheImpl($cache);
		$config->setProxyNamespace('Proxies');
		$config->setProxyDir($context->params['doctrine']['proxyDir']);
		$config->setAutoGenerateProxyClasses($context->params['productionMode']);
		$connection = $context->params['database'];
		return Doctrine\ORM\EntityManager::create($connection, $config);
	}
}
