<?php
namespace Services;
use Nette\Diagnostics\Debugger;
use Nette\DateTime;
use Entities;

/**
 * Message service class
 * @author Petr Bělohlávek
 */
class Message extends BaseService
{
	/**
	 * Creates an object as the parent does
	 * @param array
	 * @param bool
	 * @return Entities\Message
	 */
	public function create ($values, $flush = TRUE)
	{
		$values['sentTime'] = new DateTime();
		$message = parent::create($values, $flush);
		return $message;
	}

}
