<?php

/**
* Jd_Smartpost_Model_Carrier_Smartpost
*
* @version 1.0
* @copyright 2014 Henry A,gus. All rights reserved.
*
*/
class Jd_Smartpost_Model_Carrier_Smartpost extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {
	protected $_code = 'smartpost';
	
	/**
	 *
	 * @access public
	 * @return string
	 */
	public function getFormBlock() {
		return 'Jd_Smartpost_Block_Smartpost';
	}
	
	/**
	 * Collect rates
	 *
	 * @param Mage_Shipping_Model_Rate_Request $data        	
	 * @return Mage_Shipping_Model_Rate_Result
	 */
	public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
		if (! $this->getConfigFlag ( 'active' )) {
			return false;
		}
		
		$freeBoxes = 0;
		$totalPrice = 0;
		if ($request->getAllItems ()) {
			foreach ( $request->getAllItems () as $item ) {
				$totalPrice += $item->getCalculationPrice () * $item->getQty ();
			}
		}
		
		$this->setFreeBoxes ( 0 );
		$result = Mage::getModel ( 'shipping/rate_result' );
		
		$shippingPrice = $request->getPackageQty () * $this->getConfigData ( 'price' );
		$shippingPrice = $this->getFinalPriceWithHandlingFee ( $shippingPrice );
		
		// if shipping price is not empty, lets load all the places.
		
		if ($shippingPrice !== "") {
			
			$boxesList = @file_get_contents ( 'http://www.smartpost.ee/places.php' );
			
			if (strlen ( $boxesList ) > 10) {
				$boxesArray = unserialize ( $boxesList );
			}
			if (is_array ( $boxesArray )) {
				foreach ( $boxesArray as $key => $box ) {
					if ($box ['active'] == 1) {
						$boxList [$key] = $box ['name'];
					}
				}
				
				sort ( $boxList, SORT_STRING );
				// create rates
				foreach ( $boxList as $box ) {
					$method = Mage::getModel ( 'shipping/rate_result_method' );
					$method->setCarrier ( $this->_code );
					$method->setCarrierTitle ( $this->getConfigData ( 'name' ) );
					$method->setMethod ( $box );
					$method->setMethodTitle ( $box );
					$method->setPrice ( $shippingPrice );
					$method->setCost ( $shippingPrice );
					$result->append ( $method );
				}
			}
		}
		return $result;
	}
	public function getAllowedMethods() {
		return array (
				'smartpost' => $this->getConfigData ( 'name' ) 
		);
	}
}