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
 * override of the admin product controller to include accessories
 */

require_once('Mage/Adminhtml/controllers/Catalog/ProductController.php');
class Anais_Accessories_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController{
    /**
     * Get accessories products grid and serializer block
     * @access public
     * @return void
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    public function accessoriesAction(){
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.accessories')
            ->setProductsAccessories($this->getRequest()->getPost('products_accessories', null));
        $this->renderLayout();
    }
    /**
     * Get upsell products grid
     * @access public
     * @return void
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    public function accessoriesGridAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.accessories')
            ->setProductsAccessories($this->getRequest()->getPost('products_accessories', null));
        $this->renderLayout();
    }
} 