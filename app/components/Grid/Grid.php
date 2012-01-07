<?php
class Grid extends Nette\Application\UI\Control
{
	protected $templateFile = 'grid.latte';

	protected $columns;

	protected $name;

	public function __construct (Nette\ComponentModel\IContainer $parent = NULL, $name = NULL, $columns = array())
	{
		parent::__construct($parent, $name);
		asort($columns);
		$this->columns = $columns;
		$this->name = $name;
	}

	public function render ($data, $heading = NULL)
	{
		$columns = $this->columns;
		/*$columns = array();
		foreach ($this->columns as $key => $label) {
			foreach ($data as $object) {
				if (isset($object[$key]) && $object[$key] != 0) {
					$columns[$key] = $label;
				}
			}
		}*/
		$template = $this->getTemplate();
		$template->setFile(__DIR__ . '/' . $this->templateFile);
		$template->heading = $heading;
		$template->columns = $columns;
		$template->data = $data;
		$template->type = $this->name;
		$template->render();
	}
}
