<?php
class Wagento_Cart_Model_Override_Tax_Calculation  extends Mage_Tax_Model_Calculation
{
   
    /**
     * Get calculation tax rate by specific request
     *
     * @param   Varien_Object $request
     * @return  float
     */
    public function getRate($request,$calculate = true)
    {
		//return parent::getRate($request);
		if(Mage::helper('wagentocart')->isEnabled()){
			if($calculate){
				$cacheKey = $this->_getRequestCacheKey($request);
				if (!isset($this->_rateCache[$cacheKey])) {
					$this->unsRateValue();
					$this->unsCalculationProcess();
					$this->unsEventModuleId();
					Mage::dispatchEvent('tax_rate_data_fetch', array('request'=>$request));
					if (!$this->hasRateValue()) {
						$rateInfo = $this->_getResource()->getRateInfo($request);
						$this->setCalculationProcess($rateInfo['process']);
						$this->setRateValue(0);
					} else {
						$this->setCalculationProcess($this->_formCalculationProcess());
					}
					$this->_rateCache[$cacheKey] = $this->getRateValue();
					$this->_rateCalculationProcess[$cacheKey] = $this->getCalculationProcess();
				}
				return $this->_rateCache[$cacheKey];
			}
			else{
				return parent::getRate($request);
			}
		}
		else{
			return parent::getRate($request);
		}
    }
}
