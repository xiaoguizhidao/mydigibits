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
/**
 * override of the admin tabs block to include accessories
 */
class Anais_Accessories_Block_Adminhtml_Catalog_Product_Edit_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs{
	/**
	 * override the _prepareLayout() method to include teh accessories tab
	 * @access protected
	 * @return Anais_Accessories_Block_Adminhtml_Catalog_Product_Edit_Tabs
	 * @author Marius Strajeru <marius.strajeru@anais-it.com>
	 */
	protected function _prepareLayout(){
		parent::_prepareLayout();
        $product = $this->getProduct();
		if (!($setId = $product->getAttributeSetId())) {
            $setId = $this->getRequest()->getParam('set', null);
        }
        if ($setId) {
	        $this->addTab('accessories', array(
	            'label'     => Mage::helper('accessories')->__('Accessories'),
	            'url'       => $this->getUrl('*/*/accessories', array('_current' => true)),
	            'class'     => 'ajax',
	        ));
        }
        return $this;
	}
}
