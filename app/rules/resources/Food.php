<?php
namespace Rules\Resources;
use Rules\AbstractRule;

class Food extends AbstractRule implements IResource
{
	public function getDescription ()
	{
		return 'Jídlo';
	}

	public function getInitialAmount ()
	{
		return 500;
	}

	public function processExhaustion ($clan, $loss)
	{
		$this->getContext()->model->getUnitService()->setupExhaustionTimer($clan, $loss);
	}

}
