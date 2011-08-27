<?php
use Nette\Utils\Strings;

/**
 * Base class for all application presenters.
 *
 * @author     Jan "Teyras" Buchar
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	public function __call ($name, $args)
	{
		if (Strings::startsWith($name, 'get') && (Strings::endsWith($name, 'Service') || Strings::endsWith($name, 'Repository'))) {
			return $this->getService('model')->$name();
		}
		return parent::__call($name, $args);
	}
}
