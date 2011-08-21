<?php
namespace Services;
use Nette\Diagnostics\Debugger;
/**
 * Clan service class
 * @author Petr Bělohlávek
 */
class Clan extends BaseService {

	public function create ($values, $flush = TRUE)
	{
		$clan = parent::create($values, $flush);
		Debugger::barDump($clan);
		
		$fieldService = $this->context->fieldService;
		$fieldRepository = $fieldService->getRepository();
		$centralField = $fieldRepository->findByCoords(4, 3);
		$fieldService->update($centralField, array('owner' => $clan->id));
		$neighbours = $fieldRepository->getFieldNeighbours($centralField);

//		Debugger::barDump($neighbours);
		//return sth; ?

	}

	
}
