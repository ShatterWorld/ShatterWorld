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
	* @return void
	*/
	public function accept ($pathType, $offer, $targetClan)
	{
		if ($this->context->model->getResourceRepository()->checkResources($targetClan, array($offer->demand => $offer->demandAmount))){

			$clanRepository = $this->context->model->getClanRepository();
			$offerRepositary = $this->context->model->getOfferRepository();

			$profits = $offerRepositary->getMediatorProfits($pathType, $targetClan, $offer);

			$time = array('offer' => 10, 'demand' => 10);

			$this->context->model->getResourceService()->pay($targetClan, array($offer->demand => $offer->demandAmount), FALSE);
			$this->update($offer, array('sold' => true), FALSE);

			$this->context->model->getShipmentService()->create(array( //owner->target
				'type' => 'shipment',
				'timeout' => $time['offer'],
				'owner' => $offer->owner,
				'origin' => $offer->owner->getHeadquarters(),
				'target' => $targetClan->getHeadquarters(),
				'cargo' => array($offer->offer => $offer->offerAmount - $offerRepositary->getTotalMediatorProfit($targetClan, $offer))
			), FALSE);

			foreach($profits as $clanId => $profit){
				$this->context->model->getShipmentService()->create(array( //target->mediators
					'type' => 'shipment',
					'timeout' => $time['demand'],
					'owner' => $targetClan,
					'origin' => $targetClan->getHeadquarters(),
					'target' => $clanRepository->find($clanId)->getHeadquarters(),
					'cargo' => array($offer->offer => $profit)
				), FALSE);

			}

			$this->context->model->getShipmentService()->create(array( //target->owner
				'type' => 'shipment',
				'timeout' => $time['demand'],
				'owner' => $targetClan,
				'origin' => $targetClan->getHeadquarters(),
				'target' => $offer->owner->getHeadquarters(),
				'cargo' => array($offer->demand => $offer->demandAmount)
			), FALSE);
			$this->context->model->getClanService()->issueOrder($targetClan, FALSE);
			$this->entityManager->flush();
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

	/**
	 * Delete a persisted object
	 * @param Entities\BaseEntity
	 * @param bool
	 * @return void
	 */
	public function delete ($object, $flush = TRUE){
		Debugger::barDump($object);
		$this->context->model->getResourceService()->increase($object->owner, array($object->offer => $object->offerAmount));
		parent::delete($object, $flush);

	}
}

