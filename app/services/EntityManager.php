<?php
use Doctrine\ORM\ORMException,
	Doctrine\Common\EventManager,
	Doctrine\ORM\Configuration,
	Doctrine\DBAL\Connection;
	

class EntityManager extends Doctrine\ORM\EntityManager 
{
	protected $repositories = array();
	
	public function getRepository ($entityName)
	{
		$entityName = ltrim($entityName, '\\');
		if (isset($this->repositories[$entityName])) {
			return $this->repositories[$entityName];
		}
		
		$metadata = $this->getClassMetadata($entityName);
		$customRepositoryClassName = $metadata->customRepositoryClassName;

		if ($customRepositoryClassName !== null) {
			$repository = new $customRepositoryClassName($this, $metadata);
		} else {
			$repository = new Repositories\BaseRepository($this, $metadata);
		}

		$this->repositories[$entityName] = $repository;

		return $repository;
	}
	
	public static function create($conn, Configuration $config, EventManager $eventManager = null)
	{
		if (!$config->getMetadataDriverImpl()) {
			throw ORMException::missingMappingDriverImpl();
		}

		if (is_array($conn)) {
			$conn = \Doctrine\DBAL\DriverManager::getConnection($conn, $config, ($eventManager ?: new EventManager()));
		} else if ($conn instanceof Connection) {
			if ($eventManager !== null && $conn->getEventManager() !== $eventManager) {
				throw ORMException::mismatchedEventManager();
			}
		} else {
			throw new \InvalidArgumentException("Invalid argument: " . $conn);
		}

		return new static($conn, $config, $conn->getEventManager());
	}
}