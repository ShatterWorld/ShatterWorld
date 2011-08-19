<?php
namespace Services;
use Nette;

/**
 * A basic Service class
 * @author Jan "Teyras" Buchar
 */
class BaseService extends Nette\Object {
	
	/** @var Nette\DI\Container */
	protected $context;
	
	/** @var Doctrine\ORM\EntityManager */
	protected $entityManager;
	
	/** @var string */
	protected $entityClass;
	
	/**
	 * Constructor
	 * @param Doctrine\ORM\EntityManager
	 * @param string
	 */
	public function __construct ($context, $entityClass)
	{
		$this->context = $context;
		$this->entityManager = $context->entityManager;
		$this->entityClass = $entityClass;
	}
	
	/**
	 * Get the Repository (Data Access Object)
	 * @return Doctrine\ORM\EntityRepository
	 */
	public function getRepository ()
	{
		return $this->entityManager->getRepository($this->entityClass);
	}
	
	/**
	 * Fill an entity object with given values
	 * @param Entities\BaseEntity
	 * @param array
	 * @return void
	 */
	protected function fillData ($object, $values)
	{
		foreach ($values as $attribute => $value) {
			if (method_exists($object, $method = 'set' . ucfirst($attribute))) {
				$object->$method($value);
			}
		}
	}
	
	/**
	 * Create a new object
	 * @param array
	 * @param bool
	 * @return void
	 */
	public function create ($values, $flush = TRUE)
	{
		$object = new $this->entityClass;
		$this->fillData($object, $values);
		$this->entityManager->persist($object);
		if ($flush) {
			$this->entityManager->flush();
		}
	}
	
	/**
	 * Update a persisted object
	 * @param Entities\BaseEntity
	 * @param array
	 * @param bool
	 * @return void
	 */
	public function update ($object, $values, $flush = TRUE)
	{
		$this->fillData($object, $values);
		$this->entityManager->persist($object);
		if ($flush) {
			$this->entityManager->flush();
		}
	}
	
	/**
	 * Delete a persisted object
	 * @param Entities\BaseEntity
	 * @param bool
	 * @return void
	 */
	public function delete ($object, $flush = TRUE)
	{
		$this->entityManager->remove($object);
		if ($flush) {
			$this->entityManager->flush();
		}
	}
}