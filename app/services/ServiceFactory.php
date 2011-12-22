<?php
class ServiceFactory extends Nette\Object
{
	public static function createEntityManager ($container)
	{
		$config = new Doctrine\ORM\Configuration;
		$driver = $config->newDefaultAnnotationDriver($container->params['doctrine']['entityDir']);
		$cache = new Doctrine\Common\Cache\ArrayCache()/*$container->getService('doctrineCache')*/;
		$config->setMetadataDriverImpl($driver);
		$config->setMetadataCacheImpl($cache);
		$config->setQueryCacheImpl($cache);
		$config->setResultCacheImpl($cache);
		$config->setProxyNamespace($container->params['doctrine']['proxyNamespace']);
		$config->setProxyDir($container->params['doctrine']['proxyDir']);
		$config->setAutoGenerateProxyClasses(!$container->params['productionMode']);
		if (!$container->params['productionMode']) {
			$config->setSQLLogger(\Nella\Doctrine\Panel::register());
		}
		$connection = $container->params['database'];
		return EntityManager::create($connection, $config);
	}
	
	public static function createAuthorizator ($container)
	{
		$acl = new Nette\Security\Permission;
		$acl->addResource('administration');
		$acl->addRole('player');
		$acl->addRole('admin');
		$acl->allow('admin');
		return $acl;
	}
	
	public static function createModelContext ($container)
	{
		$context = new Container();
		$context->lazyCopy($container, 'user');
		$context->lazyCopy($container, 'entityManager');
		$context->lazyCopy($container, 'cacheStorage');
		$context->lazyCopy($container, 'doctrineCache');
		$context->lazyCopy($container, 'model');
		$context->lazyCopy($container, 'rules');
		$context->lazyCopy($container, 'stats');
		$context->lazyCopy($container, 'map');
		$context->params['game'] = &$container->params['game'];
		$context->params['database'] = &$container->params['database'];
		$context->params['doctrine'] = &$container->params['doctrine'];
		$context->params['appDir'] = $container->params['appDir'];
		$context->params['wwwDir'] = $container->params['wwwDir'];
		$context->params['productionMode'] = $container->params['productionMode'];
		return $context;
	}
}
