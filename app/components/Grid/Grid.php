<?php
class Grid extends Nette\Application\UI\Control
{
	protected $templateFile = 'grid.latte';

	protected $columns;

	protected $name;

	public function __construct (Nette\ComponentModel\IContainer $parent = NULL, $name = NULL, $columns = array())
	{
		parent::__construct($parent, $name);
		$this->columns = $columns;
		$this->name = $name;
	}

	public function render ($data, $heading = NULL)
	{
		$template = $this->getTemplate();
		$template->setFile(__DIR__ . '/' . $this->templateFile);
		$template->heading = $heading;
		$template->columns = $this->columns;
		$template->data = $data;
		$template->type = $this->name;
		$template->render();
	}
}
