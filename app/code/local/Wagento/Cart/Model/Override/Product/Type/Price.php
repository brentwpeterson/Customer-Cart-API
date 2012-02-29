<?php

class Wagento_Cart_Model_Override_Product_Type_Price extends Mage_Catalog_Model_Product_Type_Price
{
	/**
     * Return product base price
     *
     * @return string
     */
    public function getPrice($product)
    {
        return 3432;
    }
	
	public function getFinalPrice($qty=null, $product){
		return 3432;
	}
}