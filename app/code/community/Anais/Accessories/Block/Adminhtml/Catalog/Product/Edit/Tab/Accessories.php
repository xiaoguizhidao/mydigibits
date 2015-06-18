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
 * block for adminhtml accessories
 */
 class Anais_Accessories_Block_Adminhtml_Catalog_Product_Edit_Tab_Accessories extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * constructor
     * Set grid params
     * @access public
     * @return void
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    public function __construct(){
        parent::__construct();
        $this->setId('accessories_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->_getProduct()->getId()) {
            $this->setDefaultFilter(array('in_products'=>1));
        }
    }

    /**
     * Retirve currently edited product model
     * @access protected
     * @return Mage_Catalog_Model_Product
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    protected function _getProduct(){
        return Mage::registry('current_product');
    }

    /**
     * Add filter
     * @access protected 
     * @param object $column
     * @return Anais_Accessories_Block_Adminhtml_Catalog_Product_Edit_Tab_Accessories
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    protected function _addColumnFilterToCollection($column){
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Checks when this block is readonly
     * @access public
     * @return boolean
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    public function isReadonly(){
        return $this->_getProduct()->getAccessoriesReadonly();
    }

    /**
     * Prepare collection
     * @access protected 
     * @return Mage_Adminhtml_Block_Widget_Grid
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    protected function _prepareCollection(){
        $collection = Mage::getModel('catalog/product_link')->useAccessoriesLinks()
            ->getProductCollection()
            ->setProduct($this->_getProduct())
            ->addAttributeToSelect('*');
        if ($this->isReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = array(0);
            }
            $collection->addFieldToFilter('entity_id', array('in'=>$productIds));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     * @access protected
     * @return Mage_Adminhtml_Block_Widget_Grid
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    protected function _prepareColumns(){
        if (!$this->_getProduct()->getAccessoriesReadonly()) {
            $this->addColumn('in_products', array(
                'header_css_class' => 'a-center',
                'type'      => 'checkbox',
                'name'      => 'in_products',
                'values'    => $this->_getSelectedProducts(),
                'align'     => 'center',
                'index'     => 'entity_id'
            ));
        }

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('catalog')->__('Type'),
            'width'     => 100,
            'index'     => 'type_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name', array(
            'header'    => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width'     => 130,
            'index'     => 'attribute_set_id',
            'type'      => 'options',
            'options'   => $sets,
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('catalog')->__('Status'),
            'width'     => 90,
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('catalog')->__('Visibility'),
            'width'     => 90,
            'index'     => 'visibility',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => 80,
            'index'     => 'sku'
        ));

        $this->addColumn('price', array(
            'header'        => Mage::helper('catalog')->__('Price'),
            'type'          => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'         => 'price'
        ));

        $this->addColumn('position', array(
            'header'            => Mage::helper('catalog')->__('Position'),
            'name'              => 'position',
            'type'              => 'number',
            'width'             => 60,
            'validate_class'    => 'validate-number',
            'index'             => 'position',
            'editable'          => !$this->_getProduct()->getUpsellReadonly(),
            'edit_only'         => !$this->_getProduct()->getId()
        ));

        return parent::_prepareColumns();
    }

    /**
     * Rerieve grid URL
     * @access public
     * @return string
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    public function getGridUrl(){
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/accessoriesGrid', array('_current'=>true));
    }

    /**
     * Retrieve selected accessories products
     * @access protected
     * @return array
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    protected function _getSelectedProducts(){
        $products = $this->getProductsAccessories();
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedAccessoriesProducts());
        }
        return $products;
    }

    /**
     * Retrieve accessories products
     * @access public
     * @return array
     * @author Marius Strajeru <marius.strajeru@anais-it.com>
     */
    public function getSelectedAccessoriesProducts()
    {
        $products = array();
        foreach (Mage::registry('current_product')->getAccessoriesProducts() as $product) {
            $products[$product->getId()] = array('position' => $product->getPosition());
        }
        return $products;
    }

} 