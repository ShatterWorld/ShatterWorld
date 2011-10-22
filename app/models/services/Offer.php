<?php
namespace Services;
use Nette\Diagnostics\Debugger;
use InsufficientResourcesException;
/**
 * Offer service class
 * @author Petr Bělohlávek
 */
class Offer extends BaseService {

	/**
	* Aceppts the given offer
	* @param int
	* @param Entities\Field
	* @param array of int
	* @return void
	*/
	public function accept ($offer, $targetClan, $time)
	{
		if ($this->context->model->getResourceRepository()->checkResources($targetClan, array($offer->demand => $offer->demandAmount))){

			$this->update($offer, array('sold' => true));
			$this->context->model->getShipmentService()->create(array(
				'type' => 'shipment',
				'timeout' => $time[0],
				'owner' => $offer->owner,
				'origin' => $offer->owner->getHeadquarters(),
				'target' => $targetClan->getHeadquarters(),
				'cargo' => array($offer->offer => $offer->offerAmount)
			));

			$this->context->model->getShipmentService()->create(array(
				'type' => 'shipment',
				'timeout' => $time[1],
				'owner' => $targetClan,
				'origin' => $targetClan->getHeadquarters(),
				'target' => $offer->owner->getHeadquarters(),
				'cargo' => array($offer->demand => $offer->demandAmount)
			));
		}
		else{
			throw new InsufficientResourcesException();
		}
	}

	/**
	 * Create a new object
	 * @param array
	 * @param bool
	 * @return object
	 */
	public function create ($values, $flush = TRUE)
	{
		if($this->context->model->getResourceRepository()->checkResources($values['owner'], array($values['offer'] => $values['offerAmount']))){
			parent::create($values, $flush);
			$this->context->model->getResourceService()->pay($values['owner'], array($values['offer'] => $values['offerAmount']));
		}
		else{
			throw new InsufficientResourcesException();
		}

	}


}

