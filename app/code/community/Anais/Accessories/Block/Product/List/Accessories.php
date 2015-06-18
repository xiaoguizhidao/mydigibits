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
class Anais_Accessories_Block_Product_List_Accessories extends Mage_Catalog_Block_Product_List_Upsell{
	/**
     * Prepare accessories items data
     * @access protected
     * @return Anais_Catalog_Block_Product_List_Accessories
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    protected function _prepareData()
    {
        $product = Mage::registry('product');

        $this->_itemCollection = $product->getAccessoriesProductCollection()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addAttributeToSort('position', 'asc')
            ->addStoreFilter();

        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->_itemCollection);
    	if ($this->getItemLimit('accessories') > 0) {
            $this->_itemCollection->setPageSize($this->getItemLimit('accessories'));
        }
        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }
        return $this;
    }
	
}