<?php
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
}