<?php

/*
* use at your own risk!
* This is totally beerware, they wouldn't let me select that on the Mag. Connect site. But I'm still standing by it!
*/
/* Use module name_Shipping_Model_Carrier_class name */
class Wagento_Cart_Model_Carrier_ShippingProduct extends Mage_Shipping_Model_Carrier_Abstract
{
    /* Use group alias */
    protected $_code = 'wagento_shipping';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        // skip if not enabled
        if (!$this->isActive() || !Mage::helper('wagentocart')->isEnabled())
            return false;

        $result = Mage::getModel('shipping/rate_result');
        $handling = 0;
        if(Mage::getStoreConfig('carriers/'.$this->_code.'/handling') >0)
            $handling = Mage::getStoreConfig('carriers/'.$this->_code.'/handling');
        if(Mage::getStoreConfig('carriers/'.$this->_code.'/handling_type') == 'P' && $request->getPackageValue() > 0)
            $handling = $request->getPackageValue()*$handling;

        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier($this->_code);
        $method->setCarrierTitle(Mage::getStoreConfig('carriers/'.$this->_code.'/title'));
        /* Use method name */
        $method->setMethod('wagento_shipping');
        $method->setMethodTitle(Mage::getStoreConfig('carriers/'.$this->_code.'/methodtitle'));
		
		$shippingFee = $this->getShippingFee();
		
        $method->setCost($shippingFee);
        $method->setPrice($shippingFee);
        $result->append($method);
        return $result; // it doesnt do anything if there was an error just returns blank - maybe there should be a default shipping incase of problem? or email sysadmin?
    }
	
	public function getShippingFee(){
		$cart = Mage::helper('checkout/cart')->getCart()->getQuote();
		$shippingFee = 0;
		foreach ($cart->getAllItems() as $item) {
			$shippingItem = 0;
			if($item->getData('wagento_shipping_fee') !=''){
				$shippingItem = $item->getData('wagento_shipping_fee');
			}
			else{
				$shippingItem = Mage::helper('wagentocart')->getDefaultShippingProductFee();
				$item->setData('wagento_shipping_fee',$shippingItem);
				$item->save();
			}
			$shippingFee += $shippingItem;
		}
		return $shippingFee;
	}
}
?>