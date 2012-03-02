<?php
require_once('simple_html_dom.php');
class Wagento_Cart_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function isEnabled()
    {
        return Mage::getStoreConfig('wagentocart/general/enable');
    }
	
	public function getPriceServiceUrl($itemNumber,$accountNumber,$postalCode){
		return  Mage::getStoreConfig('wagentocart/general/service_url').'/getprice.pgm?itemnumber='.$itemNumber.'&accountnumber='.$accountNumber.'&postalcode='.$postalCode;
	}
	
	public function getPostCode(){
		$address = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
		if (empty($address) AND $address->getPostcode() == ''){
			$customerAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
			$address = Mage::getModel('customer/address')->load($customerAddressId);
		}
		return $address->getPostcode();
	}
	
	
	public function getDefaultPostCode(){
		return  Mage::getStoreConfig('wagentocart/general/default_postcode');
	}
	
	public function getWagentoTaxAmount(){
		$taxRate = 0;
		$subtotal = 0;
		$taxTotalAmount = 0;
		
		$cart = Mage::getSingleton('checkout/cart');

		foreach ($cart->getQuote()->getAllItems() as $item) {
			$itemTaxPercent =  0;
			if($item->getData('wagento_tax_fee') != ''){
				$itemTaxPercent =  $item->getData('wagento_tax_fee');
			}
			else{
				$itemTaxPercent = $this->getMagentoTaxRate($item);
				$item->setData('wagento_tax_fee',$itemTaxPercent);
				$item->save();
			}
			$itemTaxAmount = $itemTaxPercent * floatval($item->getRowTotal());
			$taxTotalAmount += $itemTaxAmount;
			$subtotal += floatval($item->getRowTotal());
		} 
		return $taxTotalAmount;
	}
	
	public function getMagentoTaxRate($quoteItem)
    {
        $product = $quoteItem->getProduct();
        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($product->getTaxClassId());
		
		$address = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
		if (empty($address) AND $address->getPostcode() == ''){
			$customerAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
			$address = Mage::getModel('customer/address')->load($customerAddressId);
		}
		
		if(!empty($address) AND $address->getPostcode() AND $address->getRegionId() AND $address->getCountryId()){
			$_request->setRegionId($address->getRegionId());
			$_request->setPostcode($address->getPostcode());
			$_request->setCountryId($address->getCountryId());
		}
		
		//print_r($_request);die;
		
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request,false);
		return floatval($currentTax)/100;
    }
	
	public function getDefaultShippingProductFee(){
		return Mage::getStoreConfig('carriers/wagento_shipping/default_shipping_product_fee');
	}
	
	public function parserData($product){
		//$product = $quoteItem->getProduct();
		$itemNumber = $product->getSku();
		$postCode = Mage::helper('wagentocart')->getPostCode();
		if(empty($postCode)){
			$postCode = Mage::helper('wagentocart')->getDefaultPostCode();
		}
		//die($itemNumber);
		$accountNumber = Mage::helper('customer')->getCustomer()->getWagentoAccountNumber();
		$url = Mage::helper('wagentocart')->getPriceServiceUrl($itemNumber,$accountNumber,$postCode);
		//echo $url;
		$this->log('webservice request',$url);
		//die($url);
		$html = file_get_html($url);
		$ret = $html->find('td[id=itemdataheader]');
		if(!empty($ret[0])){		
			$data = $ret[0]->innertext;
			$data = explode('&nbsp;:&nbsp;', $data);
			$data2 = explode('<br/>',$data[7]);
			$result = array(
				'item_number' => trim($data[0]),
				'item_description' => trim($data[1]),
				'tax_rate' => floatval(trim($data[2])),
				'tax_amount' => floatval(trim($data[3])),
				'price' => floatval(trim($data[4])),
				'shipping_fee' => floatval(trim($data[5])),
				'taxable_amount' => floatval(trim($data[6])),
				//'none_taxable_amount' => floatval(trim($data[6])),
				'freight_zone_found' => trim($data2[0]),
				'post_code' => trim($data2[1]),
				'status' => trim($data2[2]),
				 // implement later
			);
			
			$this->log('webservice response',$result);
			if($result['status'] == 'Valid'  ){
				
				return $result;
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
		
	}
	protected function log($name ='' , $data){
	
		Mage::log("--------------------". $name . "------------------------------------\n" . print_r($data, true),null,'wagento.log');
	
	}
}