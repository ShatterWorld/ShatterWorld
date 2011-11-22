<?php
use Nette\Security\Identity;
use Nette\Security\AuthenticationException;

class Authenticator extends Nette\Object implements Nette\Security\IAuthenticator {

	/** @var Services\BaseService */
	protected $userService;

	/**
	 * Constructor
	 * @param Services\BaseService
	 */
	public function __construct ($userService)
	{
		$this->userService = $userService;
	}

	/**
	 * Get the repository of User entities
	 * @return Doctrine\ORM\EntityRepository
	 */
	protected function getUserRepository ()
	{
		return $this->userService->getRepository();
	}

	/**
	 * Validate credentials against the model
	 * @param array
	 * @throws Nette\Security\AuthenticationException
	 * @return Nette\Security\IIdentity
	 */
	public function authenticate (array $credentials)
	{
		list($login, $password) = $credentials;
		$record = $this->getUserRepository()->findOneByLogin($login);
		if (!$record) {
			throw new AuthenticationException('User does not exist', self::IDENTITY_NOT_FOUND);
		}
		if (!$record->verifyPassword($password)) {
			throw new AuthenticationException('Incorrect password', self::INVALID_CREDENTIAL);
		}
		return new Identity($record->id, $record->role, $record->toArray());
	}
}
