<?php
namespace Repositories;
use Nette;
use Doctrine;

class BaseRepository extends Doctrine\ORM\EntityRepository 
{
	protected $context;
	
	public function setContext (Nette\DI\Container $context)
	{
		$this->context = $context;
	}
}