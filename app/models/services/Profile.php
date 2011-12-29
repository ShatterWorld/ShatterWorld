<?php
namespace Services;
use Nette;
use Nette\Image;
/**
 * Profile service class
 * @author Petr Bělohlávek
 */
class Profile extends BaseService {

	/**
	* Updates an object as parent does
	* In addition, if avatar-image is set, it resizes it (96x96) and saves to www/images/avatars/{profile->id}.png
	*/
	public function update ($object, $values, $flush = TRUE)
	{
		parent::update($object, $values, $flush);

		if ($values['avatar']->getError() == 0){
			$avatar = $values['avatar'];
			$avatarPath = $avatar->getTemporaryFile();
		}
		else{
			$avatarPath = $this->context->params['wwwDir'].'/images/avatars/default.png';
		}

		$avatar = Image::fromFile($avatarPath);
		$avatar->resize(96, 96);
		$targetPath = $this->context->params['wwwDir'].'/images/avatars/'.$object->id.'.png';
		$avatar->save($targetPath, 100, Image::PNG);
	}

	public function delete ($object, $flush = TRUE)
	{
		unlink($this->context->params['wwwDir'].'/images/avatars/'.$object->id.'.png');
		parent::delete($object, $flush);
	}


}
