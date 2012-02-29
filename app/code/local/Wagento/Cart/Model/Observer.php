<?php
class Wagento_Cart_Model_Observer extends Mage_Core_Model_Abstract
{ 
	public function hookCheckoutCartProductAddAfter($observer) {
		
		if(Mage::helper('wagentocart')->isEnabled()){
			$quoteItem = $observer->getQuoteItem();
			$data = Mage::helper('wagentocart')->parserData($quoteItem->getProduct());
			if(!empty($data)){
				$price = $data['price'];
				$quoteItem->setData('wagento_is_loaded',1);
				$quoteItem->setCustomPrice($price);
				$quoteItem->setOriginalCustomPrice($price);
				$quoteItem->setData('wagento_tax_fee',$data['tax_rate']);
				$quoteItem->setData('wagento_shipping_fee',$data['shipping_fee']);
			}
			else{
				$defaultShippingFee = Mage::helper('wagentocart')->getDefaultShippingProductFee();
				$defaultTaxRate = Mage::helper('wagentocart')->getMagentoTaxRate($quoteItem);
				$quoteItem->setData('wagento_tax_fee',$defaultTaxRate);
				$quoteItem->setData('wagento_shipping_fee',$defaultShippingFee);
			}
		}
		return $this;
    }
	
	public function hookCheckoutSubmitAllAfter($observer){
		if(Mage::helper('wagentocart')->isEnabled()){
			$order = $observer->getEvent()->getOrder();
			$taxAmount = Mage::helper('wagentocart')->getWagentoTaxAmount();
			$order->setTaxAmount($taxAmount);
			$grandTotal = $order->getGrandTotal() + $order->getTaxAmount();
			$order->setGrandTotal($grandTotal);
			$order->save();
		}
	}
	
	public function hookProductGetFinalPrice($observer){
		if(Mage::helper('wagentocart')->isEnabled()){
			$event = $observer->getEvent();
			$product = $event->getProduct();
			$data = Mage::helper('wagentocart')->parserData($product);
			if(!empty($data['price'])){
				$product->setFinalPrice($data['price']);
			}
		}
		return $this;
	}
	
	
	public function calculateShippingAndTax(){
		if(Mage::helper('wagentocart')->isEnabled()){
			$cart = Mage::getSingleton('checkout/cart');

			foreach ($cart->getQuote()->getAllItems() as $quoteItem) {
				$data = Mage::helper('wagentocart')->parserData($quoteItem->getProduct());
				if(!empty($data)){
					$price = $data['price'];
					$quoteItem->setData('wagento_is_loaded',1);
					$quoteItem->setCustomPrice($price);
					$quoteItem->setOriginalCustomPrice($price);
					$quoteItem->setData('wagento_tax_fee',$data['tax_rate']);
					$quoteItem->setData('wagento_shipping_fee',$data['shipping_fee']);
				}
				else{
					$defaultShippingFee = Mage::helper('wagentocart')->getDefaultShippingProductFee();
					$defaultTaxRate = Mage::helper('wagentocart')->getMagentoTaxRate($quoteItem);
					$quoteItem->setData('wagento_tax_fee',$defaultTaxRate);
					$quoteItem->setData('wagento_shipping_fee',$defaultShippingFee);
				}
				$quoteItem->save();
			}
		}
		return $this;
	}
}