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
 * override of the product model to support accessories
  */

class Anais_Accessories_Model_Product extends Mage_Catalog_Model_Product{
	/**
     * Retrieve array of accessories
     * @access public
     * @return array
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    public function getAccessoriesProducts(){
        if (!$this->hasAccessoriesProducts()) {
            $products = array();
            foreach ($this->getAccessoriesProductCollection() as $product) {
                $products[] = $product;
            }
            $this->setAccessoriesProducts($products);
        }
        return $this->getData('accessories_products');
    }

    /**
     * Retrieve accessories identifiers
     * @access public
     * @return array
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    public function getAccessoriesProductIds(){
        if (!$this->hasAccessoriesProductIds()) {
            $ids = array();
            foreach ($this->getAccessoriesProducts() as $product) {
                $ids[] = $product->getId();
            }
            $this->setAccessoriesProductIds($ids);
        }
        return $this->getData('accessories_product_ids');
    }

    /**
     * Retrieve collection accessories product
     * @access public
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection 
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    public function getAccessoriesProductCollection(){
        $collection = $this->getLinkInstance()->useAccessoriesLinks()
            ->getProductCollection()
            ->setIsStrongMode();
        $collection->setProduct($this);
        return $collection;
    }

    /**
     * Retrieve collection accessories link
     * @access public
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Collection
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    public function getAccessoriesLinkCollection(){
        $collection = $this->getLinkInstance()->useAccessoriesLinks()
            ->getLinkCollection();
        $collection->setProduct($this);
        $collection->addLinkTypeIdFilter();
        $collection->addProductIdFilter();
        $collection->joinAttributes();
        return $collection;
    }
}