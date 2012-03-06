<?php
class Wagento_Cart_Block_Cart_Totals extends Mage_Checkout_Block_Cart_Totals
{
	public function renderTotals($area = null, $colspan = 1)
	{
		if(Mage::helper('wagentocart')->isEnabled()){
			$html = '';

			$display_count = 0;
			$taxAmount = Mage::helper('wagentocart')->getWagentoTaxAmount();
		
			$taxTotal= new Varien_Object(array(
					'code'  => 'wagento_tax_fee',
					'value' => $taxAmount,
					'title'	=> 'Tax'
			));
			$isGrandTotal = false;
			foreach($this->getTotals() as $total) {
				if ($total->getArea() != $area && $area != -1) {
					continue;
				}
				
				switch($total->getCode()){
					case 'tax': 
						break;
					case 'grand_total':
							if($display_count ==0){
								$grandTotal = $total->getValue()+$taxAmount;
								$total->setValue($grandTotal);
							}
							
							$html .= $this->renderTotal($total, $area, $colspan);
							$display_count++;
							$isGrandTotal = true;
						break;
					default:
							$html .= $this->renderTotal($total, $area, $colspan);
						break;
				}
			}
			if($taxAmount > 0 AND !$isGrandTotal){
				$html .= $this->renderTotal($taxTotal, $area, $colspan);
			}
			return $html;
		}
		else{
			return parent::renderTotals($area,$colspan);
		}
	}
}
