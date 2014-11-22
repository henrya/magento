<?php
/**
* Jd_Smartpost_Block_Smartpost
*
* @version 1.0
* @author Henry Algus <henryalgus@gmail.com>
*
*/
class Jd_Smartpost_Block_Smartpost extends Mage_Checkout_Block_Onepage_Shipping_Method_Available {
	/**
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->setTemplate ( 'smartpost/form.phtml' );
	}
	
	/**
	 * getMethodTitle function.
	 *
	 * @access public
	 * @return string
	 */
	public function getMethodTitle() {
		return Mage::getStoreConfig ( 'carriers/smartpost/name' );
	}
}