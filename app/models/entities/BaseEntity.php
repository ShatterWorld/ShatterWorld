<?php
namespace Entities;
use Nette;

/**
 * Universal entity ancestor
 * @MappedSuperclass
 * @author Jan "Teyras" Buchar
 */
abstract class BaseEntity extends Nette\Object {

	/**
	 * @Id @GeneratedValue
	 * @Column(type = "integer")
	 * @var int
	 */
	private $id;

	/**
	 * ID getter
	 * @return int
	 */
	final public function getId ()
	{
		return $this->id;
	}

	/**
	 * Get the visible values of the entity as an associative array
	 * @return array
	 */
	public function toArray (&$visited = array())
	{
		$result = array();
		foreach (get_class_methods($this) as $method) {
			if (Nette\Utils\Strings::startsWith($method, 'get')) {
				if (!is_object($value = $this->{$method}())) {
					$result[lcfirst(substr($method, 3))] = $value;
				} elseif (method_exists($value, 'toArray')) {
					if (!in_array($value, $visited, TRUE)) {
						$visited[] = $value;
						$result[lcfirst(substr($method, 3))] = $value->toArray($visited);
					}
				}
			}
		}
		return $result;
	 }
}
