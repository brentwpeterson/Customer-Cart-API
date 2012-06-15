<?php
class Wagento_Cart_Model_Override_Tax_Sales_Total_Quote_Tax extends Mage_Tax_Model_Sales_Total_Quote_Tax
{
    /**
     * Tax caclulation for shipping price
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   Varien_Object $taxRateRequest
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    protected function _calculateShippingTax(Mage_Sales_Model_Quote_Address $address, $taxRateRequest)
    {
        $shippingMethod = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingMethod();
		//print_r($shippingMethod);
		$wagentoShippingRates  = array(
			'wagento_shipping_freightGrnd',
			'wagento_shipping_freight2Day',
			'wagento_shipping_freight1Day'
		);
		if(in_array($shippingMethod, $wagentoShippingRates)){
			
			
			$rates  = Mage::getSingleton('core/session')->getCurrentShippingRates();
			
			if(!empty($rates)){
		
				$taxAmount = 0;
				$shippingMethod = str_replace('wagento_shipping_','',$shippingMethod);
				if(isset($rates[$shippingMethod]['tax'])){
					$taxAmount = $rates[$shippingMethod]['tax'];
					
					$taxRateRequest->setProductClassId($this->_config->getShippingTaxClass($this->_store));
					$rate               = $this->_calculator->getRate($taxRateRequest);
					$inclTax            = $address->getIsShippingInclTax();
					$shipping           = $address->getShippingTaxable();
					$baseShipping       = $address->getBaseShippingTaxable() ;

					$hiddenTax     = null;
					$baseHiddenTax = null;
					switch ($this->_helper->getCalculationSequence($this->_store)) {
						case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
						case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
							$tax             = $this->_calculator->calcTaxAmount($shipping, $rate, $inclTax, false, true);
							$baseTax         = $this->_calculator->calcTaxAmount($baseShipping, $rate, $inclTax, false, true);
							break;
						case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
						case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
							$discountAmount     = $address->getShippingDiscountAmount();
							$baseDiscountAmount = $address->getBaseShippingDiscountAmount();
							$tax        = $this->_calculator->calcTaxAmount($shipping - $discountAmount, $rate, $inclTax, false, true);
							$baseTax    = $this->_calculator->calcTaxAmount($baseShipping - $baseDiscountAmount, $rate, $inclTax, false, true);
							break;
					}

					if ($this->_config->getAlgorithm($this->_store) == Mage_Tax_Model_Calculation::CALC_TOTAL_BASE) {
						$tax        = $this->_deltaRound($tax, $rate, $inclTax);
						$baseTax    = $this->_deltaRound($baseTax, $rate, $inclTax, 'base');
					} else {
						$tax        = $this->_calculator->round($tax);
						$baseTax    = $this->_calculator->round($baseTax);
					}
					if ($inclTax && !empty($discountAmount)) {
						$hiddenTax      = $shipping - $tax - $address->getShippingAmount();
						$baseHiddenTax  = $baseShipping - $baseTax - $address->getBaseShippingAmount();
					}

					// set tax = 0 for shipping fee
					$tax = $taxAmount;
					$baseTax = $taxAmount;
					$hiddenTax = $taxAmount;
					$baseHiddenTax = $taxAmount; 
					
					$this->_addAmount(max(0, $tax));
					$this->_addBaseAmount(max(0, $baseTax));
					$address->setShippingTaxAmount(max(0, $tax));
					$address->setBaseShippingTaxAmount(max(0, $baseTax));
					$address->setShippingHiddenTaxAmount(max(0, $hiddenTax));
					$address->setBaseShippingHiddenTaxAmount(max(0, $baseHiddenTax));
					$address->addTotalAmount('shipping_hidden_tax', $hiddenTax);
					$address->addBaseTotalAmount('shipping_hidden_tax', $baseHiddenTax);
					$applied = $this->_calculator->getAppliedRates($taxRateRequest);
					$this->_saveAppliedTaxes($address, $applied, $tax, $baseTax, $rate);
					return $this;
				}
			}
			else{
				return parent::_calculateShippingTax($address, $taxRateRequest);
			}
		}
		else{
			return parent::_calculateShippingTax($address, $taxRateRequest);
		}
    }
}
