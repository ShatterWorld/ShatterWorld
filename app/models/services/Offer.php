<?php
namespace Services;
use Nette\Diagnostics\Debugger;
/**
 * Offer service class
 * @author Petr Bělohlávek
 */
class Offer extends BaseService {

	/**
	* Aceppts the given offer
	* @param int
	* @return void
	*/
	public function accept ($offer)
	{
		$this->update($offer, array('sold' => true));
		/*should generate the event*/
	}
}
