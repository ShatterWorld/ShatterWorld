<?php
namespace Stats;
use Entities;

class Orders extends AbstractStat
{
	/**
	 * Get the count of available orders for given clan
	 * @param Entities\Clan
	 * @return int
	 */
	public function getAvailableOrders (Entities\Clan $clan)
	{
		$now = new \DateTime();
		$cap = $this->getContext()->params['game']['stats']['orderCap'];
		$diff = $now->format('U') - $this->getContext()->params['game']['start']->format('U');
		$orders = (intval($diff / $this->getContext()->params['game']['stats']['orderTime'])) - $clan->orders->issued - $clan->orders->expired;
		if ($orders > $cap) {
			$this->getContext()->model->getOrderService()->update($clan->orders, array(
				'expired' => $clan->orders->expired + ($orders - $cap)
			));
			return $cap;
		}
		return $orders;
	}

	/**
	 * Get the time remaining until another order is received
	 * @return int
	 */
	public function getTimeout ()
	{
		$now = new \DateTime();
		$orderTime = $this->getContext()->params['game']['stats']['orderTime'];
		return $orderTime - (($now->format('U') - $this->getContext()->params['game']['start']->format('U')) % $orderTime);
	}
}