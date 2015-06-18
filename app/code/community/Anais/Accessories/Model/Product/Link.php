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
  * override of the Mage_Catalog_Model_Product_Link in orer to support the new association
  */
class Anais_Accessories_Model_Product_Link extends Mage_Catalog_Model_Product_Link{
	const LINK_TYPE_ACCESSORIES   = 6;
	/**
	 * tell the model to use accessories
	 * @access public
	 * @return Anais_Catalog_Model_Product_Link 
	 * @author Marius Strajeru <marius.strajeru@anais-it.com>
	 */
    public function useAccessoriesLinks(){
        $this->setLinkTypeId(self::LINK_TYPE_ACCESSORIES);
        return $this;
    }

    /**
     * Save data for product relations
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Link
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    public function saveProductRelations($product)
    {
    	parent::saveProductRelations($product);
        $data = $product->getAccessoriesLinkData();
        if (!is_null($data)) {
            $this->_getResource()->saveProductLinks($product, $data, self::LINK_TYPE_ACCESSORIES);
        }
        return $this;
    }
	
}