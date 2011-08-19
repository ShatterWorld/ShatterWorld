<?php
namespace Services;
use Nette;
use Nette\Diagnostics\Debugger;
/**
 * Profile service class
 * @author Petr Bělohlávek
 */
class Profile extends BaseServise {

	public function update ($object, $values, $flush = TRUE)
	{
		$values['icq']="123";
		parent::update($object, $values);
		
		$avatarPath=$values['avatar'];

		$avatar=Image::fromFile($avatarPath);
		$avatar->save('{$basePath}/images/avatars/a.jpg');

		$tmp="ahoj";
		Debugger::barDump($tmp);
	}
	
	
	
}
