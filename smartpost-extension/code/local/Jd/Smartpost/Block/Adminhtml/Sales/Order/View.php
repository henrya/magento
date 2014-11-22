<?php
/**
* Jd_Smartpost_Block_Adminhtml_Sales_Order_View
*
* @version 1.0
* @author Henry Algus <henryalgus@gmail.com>
*
*/
class Jd_Smartpost_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View {
	/**
	 * Creates SmartPost button for API delivery
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$url = $this->getSmartpostUrl ();
		$this->_addButton ( 'smartbutton', array (
				'label' => Mage::helper ( 'Sales' )->__ ( 'Send to SmartPost' ),
				'onclick' => "new Ajax.Request('" . $url . "', {method: 'post',parameters: 'arams_Here',onComplete: function(transport) {}});",
				'class' => 'go' 
		), 0, 100, 'header', 'header' );
		parent::__construct ();
	}
	
	/**
	 * getSmartpostUrl function.
	 *
	 * @access public
	 * @return string
	 */
	public function getSmartpostUrl() {
		return $this->getUrl ( '*/*/smartpost' );
	}
}


