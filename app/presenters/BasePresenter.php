<?php
use Nette\Utils\Strings;

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	public function __call ($name, $args)
	{
		if (Strings::startsWith($name, 'get')) {
			if (Strings::endsWith($name, 'Service')) {
				return $this->context->getService(lcfirst(substr($name, 3)));
			} elseif (Strings::endsWith($name, 'Repository')) {
				return $this->context->getService(lcfirst(substr($name, 3, -10)) . 'Service')->getRepository();
			}
		}
		return parent::__call($name, $args);
	}
}
