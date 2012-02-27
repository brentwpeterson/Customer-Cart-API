<?php

class Wagento_Cart_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function isEnabled()
    {
        return Mage::getStoreConfig( self::XML_PATH_ENABLED );
    }
	
	public function getPriceServiceUrl($itemNumber,$accountNumber,$orderamount,$postalcode,$pricecode=10){

        $url =  Mage::getStoreConfig('wagentocart/general/service_url');
        $url.= '/getprice.pgm?itemnumber='.$itemNumber;
        $url.= '&accountnumber='.$accountNumber;
        $url.= '&orderamount='.$orderamount;
        $url.= '&postalcode='.$postalcode;
        $url.= '&pricecode='.$pricecode;

        return $url;
	}
	
}