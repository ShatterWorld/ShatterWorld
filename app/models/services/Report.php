<?php
namespace Services;
use Nette;
use Nette\Diagnostics\Debugger;
/**
 * Report service class
 * @author Petr Bělohlávek
 */
class Report extends BaseService {

	public function markRead ($report)
	{
		$this->update($report, array('read' => true));
	}

	public function markReadAll ($clan)
	{
		foreach($this->context->model->getReportRepository()->findByOwner($clan) as $report){
			$this->markRead($report);
		}
	}


}
