<?php
/**
 * Anais_Accessories extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Anais
 * @package    Anais_Accessories
 * @copyright  Copyright (c) 2011 Anais Software Services
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
 /**
 * @category   Anais
 * @package    Anais_Accessories
 * @author     Marius Strajeru <marius.strajeru@anais-it.com>
 */ 
$installer = $this;
$installer->startSetup();
$linkTypeId = Anais_Accessories_Model_Product_Link::LINK_TYPE_ACCESSORIES;
$installer->run("INSERT INTO {$installer->getTable('catalog/product_link_type')} SET link_type_id = '{$linkTypeId}', code = 'accessories'");
$installer->run("INSERT INTO {$installer->getTable('catalog/product_link_attribute')} SET link_type_id = '{$linkTypeId}', `product_link_attribute_code` = 'position', `data_type` = 'int'");
$installer->endSetup();