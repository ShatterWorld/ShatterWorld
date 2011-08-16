<?php
class DoctrineCache extends Doctrine\Common\Cache\AbstractCache {
	
	/** @var Nette\Caching\Cache */
	protected $storage;
	
	/**
	 * Constructor
	 * @param Nette\Caching\IStorage
	 */
	public function __construct (Nette\Caching\IStorage $storage)
	{
		$this->storage = new Nette\Caching\Cache($storage, 'Doctrine');
	}
	
	/** 
	 *{@inheritDoc} 
	 */
	public function getIds ()
	{
		return array_keys($this->storage);
	}
	
	/** 
	 *{@inheritDoc} 
	 */
	protected function _doFetch ($id)
	{
		$data = $this->storage->load($id);
		return $data !== NULL ? $data : FALSE;
	}
	
	/** 
	 *{@inheritDoc} 
	 */
	protected function _doContains ($id)
	{
		$data = $this->storage->load($id);
		return $data !== NULL;
	}
	
	/** 
	 *{@inheritDoc} 
	 */
	protected function _doSave ($id, $data, $lifeTime = FALSE)
	{
		$deps = array();
		if ($data instanceof Doctrine\ORM\Mapping\ClassMetadata) {
			$files = array();
			foreach ((array($data->name) + $data->parentClasses) as $class) {
				$files[] = Nette\Reflection\ClassType::from($class)->getFilename();
			}
			$deps['files'] = $files;
		}
		if ($lifeTime != 0) {
			$deps['expire'] = time() + $lifeTime;
		}
		$this->storage->save($id, $data, $deps);
		return TRUE;
	}
	
	/** 
	 *{@inheritDoc} 
	 */
	protected function _doDelete ($id)
	{
		$this->storage->save($id, NULL);
		return TRUE;
	}
}