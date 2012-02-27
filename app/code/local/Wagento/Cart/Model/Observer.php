<?php
require_once('simple_html_dom.php');
class Wagento_Cart_Model_Observer extends Mage_Core_Model_Abstract
{ 
	public function hookCheckoutCartProductAddAfter($observer) {
		
		$quoteItem = $observer->getQuoteItem();
		if($quoteItem->getData('wagento_is_loaded')!= '1'){
			$data = $this->parserData($quoteItem);
			if(!empty($data)){
				$price = $data['price'];
				$quoteItem->setData('wagento_is_loaded',1);
				$quoteItem->setCustomPrice($price);
				$quoteItem->setOriginalCustomPrice($price);
				$quoteItem->setData('wagento_tax_fee',rand(1,300));
				$quoteItem->setData('wagento_shipping_fee',rand(1,200));
				$quoteItem->setBaseTaxAmount(20);
                $quoteItem->setTaxAmount(20);
			}
		}
    }
	public function parserData($quoteItem){
		$product = $quoteItem->getProduct();
		$itemNumber = $product->getSku();//'9780763838782';
		//die($itemNumber);
		$accountNumber = '0270850';//Mage::helper('customer')->getCustomer()->getAccountNumber();
		$url = Mage::helper('wagentocart')->getPriceServiceUrl($itemNumber,$accountNumber);
		$html = file_get_html($url);
		$ret = $html->find('td[id=itemdataheader]'); 
		$data = $ret[0]->innertext;
		$data = explode('&nbsp;:&nbsp;', $data);
		$data2 = explode('<br/>',$data[7]);
		$result = array(
			'item_number' => trim($data[0]),
			'item_description' => trim($data[1]),
			'tax_rate' => floatval(trim($data[2])),
			'tax_amount' => floatval(trim($data[3])),
			'price' => floatval(trim($data[4])),
			'taxable_amount' => floatval(trim($data[5])),
			'none_taxable_amount' => floatval(trim($data[6])),
			'freight_zone_found' => trim($data2[0]),
			'post_code' => trim($data2[1]),
			'status' => trim($data2[2])
		);
		if($result['status'] == 'Valid'  ){
			//print_r($result);die;
			return $result;
		}
		else{
			return false;
		}
	}
}