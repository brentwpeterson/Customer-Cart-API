<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('sales/quote_item')} ADD COLUMN `wagento_shipping_fee` decimal(12,4) DEFAULT NULL COMMENT 'Wagento shipping fee'; 
ALTER TABLE {$this->getTable('sales/quote_item')} ADD COLUMN `wagento_tax_fee` decimal(12,4) DEFAULT NULL COMMENT 'Wagento tax fee'; 
ALTER TABLE {$this->getTable('sales/quote_item')} ADD COLUMN `wagento_is_loaded` BOOLEAN NOT NULL DEFAULT '0'
");	

/*
 * Add Customer Attribute 'wagento_account_number'
 */
$installer->getConnection()->addColumn($this->getTable('customer/entity'), 'wagento_account_number', "VARCHAR(255) DEFAULT NULL AFTER group_id");

/*
 * Add Customer Attribute 'wagento_account_number'
 */
$attribute = Mage::getModel('eav/entity_attribute')
            	->loadByCode(Mage::getModel('eav/entity')->setType('customer')->getTypeId(), 'wagento_account_number');

if (!$attribute->getId()) {
	$attribute = Mage::getModel('eav/entity_attribute');
}

$attribute
	->setEntityTypeId(Mage::getModel('eav/entity')->setType('customer')->getTypeId())
	->setAttributeCode('wagento_account_number')
	->setBackendType(Mage::getModel('eav/entity_attribute')->TYPE_STATIC)
	->setFrontendInput('text')
	->setFrontendLabel('Wagento account number')
	->setIsGlobal(1)
	->setIsVisible(1)
	->setIsConfigureable(1)
	->setAttributeSetId(1)
	->setAttributeGroupId(1);

$attribute->save();

/*
 * Add attribute configuration for Magento 1.4+
 */
$version = explode('.', Mage::getVersion());
if (isset($version[0]) && isset($version[1]) && $version[0]==1 && $version[1]>=4) {
	$eavConfig = Mage::getSingleton('eav/config');
	if ($attribute = $eavConfig->getAttribute('customer', 'wagento_account_number')) {
		$attribute->setData('used_in_forms', array('adminhtml_customer'));
		$attribute->setData('input_filter', '');
		$attribute->setData('multiline_count', 0);
		$attribute->setData('is_system', 1);
		$attribute->setData('sort_order', 101);
		$attribute->save();
	}
}

$installer->endSetup();