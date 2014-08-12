<?php
/**
* Jd_Smartpost_Adminhtml_SmartpostController
*
* @version 1.0
* @copyright 2014 Henry Algus. All rights reserved.
*
*/
class Jd_Smartpost_Adminhtml_SmartpostController extends Mage_Adminhtml_Controller_Action {
	protected function _construct() {
		// $this->setUsedModuleName('Jd_Smartpost');
	}
	
	/**
	 *
	 * @access public
	 * @return void
	 */
	public function indexAction() {
		$this->_redirect ( '/' );
	}
	
	/**
	 * Exports order to SmartPost.
	 *
	 * @access public
	 * @return void
	 */
	public function exportAction() {
		if ($this->getRequest ()->isXmlHttpRequest ()) {
			$orderId = $this->getRequest ()->getParam ( 'order_id' );
			$order = $this->getOrder ( $orderId );
			$result = array (
					"saved" => 1 
			);
			// is order payed
			if ($order instanceof Mage_Sales_Model_Order && ($order->getState () == Mage_Sales_Model_Order::STATE_PROCESSING || $order->getState () == Mage_Sales_Model_Order::STATE_COMPLETE)) {
				
				$items = $order->getAllItems ();
				$name = array ();
				foreach ( $items as $itemId => $item ) {
					$name [] = $item->getName ();
				}
				
				// get configuration data and prepare for API-delivery
				
				$uri = Mage::getStoreConfig ( 'carriers/smartpost/service_uri' );
				$username = Mage::getStoreConfig ( 'carriers/smartpost/username' );
				$password = Mage::getStoreConfig ( 'carriers/smartpost/password' );
				
				$shipping = $order->getShippingAddress ();
				$place = str_replace ( "SmartPOST - ", "", $order->getShippingDescription () );
				
				// load all the boxes
				
				$boxesList = @file_get_contents ( 'http://www.smartpost.ee/places.php' );
				
				if (strlen ( $boxesList ) > 10) {
					$boxesArray = unserialize ( $boxesList );
				}
				
				$placeId = 0;
				
				if (is_array ( $boxesArray )) {
					foreach ( $boxesArray as $box ) {
						if ($box ['name'] == $place) {
							$placeId = $box ['place_id'];
							break;
						}
					}
				}
				
				// create SmartPost request while using order data
				
				$orderData = array (
						array (
								"place_id" => $placeId,
								"content" => implode ( ",", $name ),
								"name" => $shipping->getFirstname () . " " . $shipping->getLastname (),
								"phone" => $shipping->getTelephone (),
								"email" => $shipping->getEmail (),
								"cash" => "0",
								"ref" => $order->getRealOrderId (),
								"lang" => "et" 
						) 
				);
				
				// Send request to the SmartPost server
				
				if ($placeId > 0) {
					$client = new Zend_Http_Client ( $uri );
					$client->setAuth ( $username, $password );
					$response = $client->setRawData ( Mage::helper ( 'core' )->jsonEncode ( $orderData ), 'application/json' )->request ( 'POST' );
					$comment = 'Order sent to Smartpost';
					$order->addStatusHistoryComment ( $comment );
					$order->save ();
				}
			} else {
				$result ['saved'] = 0;
			}
			$this->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $result ) );
		}
	}
	
	/**
	 * Load order by ID
	 *
	 * @access public
	 * @param mixed $orderId        	
	 * @return sales/order
	 */
	public function getOrder($orderId) {
		return Mage::getModel ( 'sales/order' )->load ( $orderId );
	}
}
