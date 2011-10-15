<?php
class DoctrineCache extends Doctrine\Common\Cache\AbstractCache {
	
	/** @var Nette\Caching\Cache */
	protected $storage;
	
	/** @var Nette\Caching\Cache */
	protected $ids;
	
	/** @var string */
	const IDS_KEY = 'keylist';
	
	/**
	 * Constructor
	 * @param Nette\Caching\IStorage
	 */
	public function __construct (Nette\Caching\IStorage $storage)
	{
		$this->storage = new Nette\Caching\Cache($storage, 'Doctrine');
		$this->ids = $this->storage->derive('IDs');
	}
	
	/**
	 * Add an item to the journal of IDs
	 * @param scalar
	 * @param int
	 * @return void
	 */
	protected function addId ($id, $lifetime = 0)
	{
		$ids = $this->ids->load(static::IDS_KEY);
		if (!isset($ids[$id]) || $ids[$id] !== ($lifetime ?: TRUE)) {
			$ids[$id] = $lifetime ?: TRUE;
			$this->ids->save(static::IDS_KEY, $ids);
		}
	}
	
	/**
	 * Remove an item from the journal of IDs
	 * @param scalar
	 * @return void
	 */
	protected function removeId ($id)
	{
		$ids = $this->ids->load(static::IDS_KEY);
		if (isset($ids[$id])) {
			unset($ids[$id]);
			$this->ids->save(static::IDS_KEY, $ids);
		}
	}
	
	/** 
	 *{@inheritDoc} 
	 */
	public function getIds ()
	{
		$ids = (array) $this->ids->load(static::IDS_KEY);
		$validIds = array_filter($ids, function ($expiry) {
			return !($expiry > 0 && time() > $expiry);
		});
		if ($ids !== $validIds) {
			$this->ids->save(static::IDS_KEY, $validIds);
		}
		return $validIds;
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
	protected function _doSave ($id, $data, $lifeTime = 0)
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
		$this->addId($id, $lifeTime ? $lifeTime + time() : 0);
		return TRUE;
	}
	
	/** 
	 *{@inheritDoc} 
	 */
	protected function _doDelete ($id)
	{
		unset($this->storage[$id]);
		$this->removeId($id);
		return TRUE;
	}
}