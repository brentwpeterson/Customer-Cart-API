<?php
class Wagento_Cart_Model_Override_Checkout_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    /**
     * Save billing address information to quote
     * This method is called by One Page Checkout JS (AJAX) while saving the billing information.
     *
     * @param   array $data
     * @param   int $customerAddressId
     * @return  Mage_Checkout_Model_Type_Onepage
     */
    public function saveBilling($data, $customerAddressId)
    {
		$result = parent::saveBilling($data, $customerAddressId);
		Mage::dispatchEvent(
            'checkout_onepage_save_billing_after',
            array('sender'=>$this)
        );
		return $result;
    }


    /**
     * Save checkout shipping address
     *
     * @param   array $data
     * @param   int $customerAddressId
     * @return  Mage_Checkout_Model_Type_Onepage
     */
    public function saveShipping($data, $customerAddressId)
    {
        $result = parent::saveShipping($data, $customerAddressId);
		Mage::dispatchEvent(
            'checkout_onepage_save_shipping_after',
            array('sender'=>$this)
        );
		return $result;
    }
}
