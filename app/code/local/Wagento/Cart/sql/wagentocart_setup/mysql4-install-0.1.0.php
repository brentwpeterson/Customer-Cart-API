<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('sales/lat_quote_item')}` ADD COLUMN `wagento_shipping_fee` decimal(12,4) DEFAULT NULL COMMENT 'Wagento shipping fee';
ALTER TABLE `{$this->getTable('sales/lat_quote_item')}` ADD COLUMN `wagento_tax_fee` decimal(12,4) DEFAULT NULL COMMENT 'Wagento tax fee';
ALTER TABLE `{$this->getTable('sales/lat_quote_item')}` ADD `wagento_is_loaded` BOOLEAN NOT NULL DEFAULT '0';
");	
$installer->endSetup();