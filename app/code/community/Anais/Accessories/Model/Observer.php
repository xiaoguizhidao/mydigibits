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
class Anais_Accessories_Model_Observer{
	/**
	 * set accessories for save
	 * @access public
	 * @param Varien_Object $observer
	 * @return Anais_Accessories_Model_Observer
	 * @author Marius Strajeru <marius.strajeru@anais-it.com>
	 */
	public function beforeProductSave($observer){
		
		$product = $observer->getEvent()->getProduct();
		$links = $observer->getEvent()->getRequest()->getPost('links');
		if (isset($links['accessories']) && !$product->getAccessoriesReadonly()) {
            $product->setAccessoriesLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['accessories']));
        }
        return $this;
	}	
}