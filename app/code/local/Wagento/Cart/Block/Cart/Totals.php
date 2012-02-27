<?php
class Wagento_Cart_Block_Cart_Totals extends Mage_Checkout_Block_Cart_Totals
{
	public function renderTotals($area = null, $colspan = 1)
	{
		$html = '';

		$display_count = 0;
		
		//print_r($this->getTotals());
		foreach($this->getTotals() as $total) {
			if ($total->getArea() != $area && $area != -1) {
				continue;
			}
			
			// Add Sales Tax line
			if ($total->getCode() == "tax") {
					$total->setTitle('Sales Tax');
					$total->setValue(50);
					$html .= $this->renderTotal($total, $area, $colspan);

			} else {
				$html .= $this->renderTotal($total, $area, $colspan);
				//$html .= $total->getCode();
			}
		}
		return $html;
	}
}
