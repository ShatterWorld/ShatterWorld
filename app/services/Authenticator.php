<?php
use Nette\Security\Identity;
use Nette\Security\AuthenticationException;

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
	 * @throws Nette\Security\AuthenticationException
	 * @return Nette\Security\IIdentity
	 */
	public function authenticate (array $credentials)
	{
		list($nickname, $password) = $credentials;
		$record = $this->getUserRepository()->findOneByNickname($nickname);
		if (!$record) {
			throw new AuthenticationException('User does not exist', self::IDENTITY_NOT_FOUND);
		}
		if (!$record->verifyPassword($password)) {
			throw new AuthenticationException('Incorrect password', self::INVALID_CREDENTIAL);
		}
		return new Identity($record->id, $record->role, $record->toArray());
	}
}