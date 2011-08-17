<?php
class Authenticator extends Nette\Object implements Nette\Security\IAuthenticator {
	
	/** @var Doctrine\ORM\EntityManager */
	protected $entityManager;
	
	/**
	 * Constructor
	 * @param Doctrine\ORM\EntityManager
	 */
	public function __construct ($entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	/**
	 * Get the repository of User entities
	 * @return Doctrine\ORM\EntityRepository
	 */
	protected function getUserRepository ()
	{
		return $this->entityManager->getRepository('Entities\User');
	}
	
	/**
	 * Validate credentials against the model
	 * @param array
	 * @return Nette\Security\IIdentity
	 */
	public function authenticate ($credentials)
	{
		
	}
}