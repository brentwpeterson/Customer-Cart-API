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

        
		$rates = $this->getAllShippingRates($request);
		
		foreach($rates as $rate){
			$method = Mage::getModel('shipping/rate_result_method');
			$method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
			/* Use method name */
			$method->setMethod($rate['method']);
			$method->setMethodTitle($rate['method_title']);
			
			
			$method->setCost($rate['cost']);
			$method->setPrice($rate['price']);
			$result->append($method);
		}
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
	
	public function getAllShippingRates($request){
		$cart = Mage::helper('checkout/cart')->getCart()->getQuote();
		$rates = array();
		$accountNumber = Mage::helper('customer')->getCustomer()->getId();
		$orderquantity = 3;
		
		$postcode = Mage::helper('wagentocart')->getPostCode();
		if(empty($postcode)){
			$postcode = Mage::helper('wagentocart')->getDefaultPostCode();
		}
		$orderweight = $this->getTotalWeight($request);
		$pricecode = '';
		$orderamount = $this->getOrderAmount($request);
		$promotion = '';
		$ordernumber = 1;
		$outputmode = 'XML' ;
		
		$params = array(
			'itemnumber'=> 'SHIPPING',
			'accountnumber'=> $accountNumber,
			'orderquantity'=> $orderquantity,
			'orderweight'=> $orderweight,
			'postalcode'=>$postcode,
			'pricecode'=> $pricecode,
			'orderamount'=> $orderamount,
			'promotion'=> $promotion,
			'ordernumber'=> $ordernumber,
			'outputmode'=> $outputmode
		);
		
		try{
			$uriRequest = Mage::helper('wagentocart')->getShippingRequestUrl($params);
			Mage::helper('wagentocart')->log('Shipping Uri',$uriRequest);
			
			$content = file_get_contents($uriRequest);
			
			Mage::helper('wagentocart')->log('Shipping Response ',$content);
			
			$xml = simplexml_load_string($content);

			$rates['freight1DayNoTax'] = array(
				'method' => 'freight1DayNoTax',
				'method_title' => 'Next Day Air',
				'cost' => $xml->freightdata->freight1DayNoTax,
				'price' => $xml->freightdata->freight1DayNoTax,
			);
			
			$rates['freight2DayNoTax'] = array(
				'method' => 'freight2DayNoTax',
				'method_title' => '2nd Day Air',
				'cost' => $xml->freightdata->freight2DayNoTax,
				'price' => $xml->freightdata->freight2DayNoTax,
			);
			
			$rates['freightGrndNoTax'] = array(
				'method' => 'freightGrndNoTax',
				'method_title' => 'Ground',
				'cost' => $xml->freightdata->freightGrndNoTax,
				'price' => $xml->freightdata->freightGrndNoTax,
			);
			
		}
		catch(Exception $e){
			Mage::helper('wagentocart')->log('Shipping Error ',$e->getMessage());
		}
		
		if(empty($rates)){
			
			$rates['standard'] = array(
				'method' => 'standard',
				'method_title' => 'Standard',
				'cost' => Mage::helper('wagentocart')->getDefaultShippingProductFee(),
				'price' => Mage::helper('wagentocart')->getDefaultShippingProductFee(),
			);
		
		}
		
		return $rates;
		
	}
	
	public function getOrderAmount($request){
 		$orderAmount = 0;
 		if ($request->getAllItems()) {
	 		foreach($request->getAllItems() as $item)
	 		{
				$orderAmount += floatval($item->getRowTotal());	 			
	 		}
 		}
 		return $orderAmount;
 	}
	
	public function getTotalWeight($request){
		
		return $request->getPackageWeight();
		$totalWeight = 0;
 		if ($request->getAllItems()) {
	 		foreach($request->getAllItems() as $item)
	 		{
				$totalWeight += floatval($item->getRowWeight());	 	
				
	 		}
 		}
 		return $totalWeight;
	
	}
	
}
?>