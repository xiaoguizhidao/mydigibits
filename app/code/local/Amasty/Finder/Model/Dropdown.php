<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */  
class Amasty_Finder_Model_Dropdown extends Mage_Core_Model_Abstract
{
    public function _construct()
    {    
        $this->_init('amfinder/dropdown');
    }
    
    public function getValues($parentId, $selected=0)
    {
        $options[] = array(
            'value'    => 0, 
            'label'    => Mage::helper('amfinder')->__('Please Select ...'),
            'selected' => false,
        );
        
        $collection = Mage::getModel('amfinder/value')->getCollection()
            ->addFieldToFilter('parent_id', $parentId);
            
        if (!$this->getPos()){
            $collection->addFieldToFilter('dropdown_id', $this->getId());    
        }
        switch ($this->getSort()) {
            case Amasty_Finder_Helper_Data::SORT_STRING_ASC :
                $order = 'name ASC'; 
                break;
            case Amasty_Finder_Helper_Data::SORT_STRING_DESC :
                $order = 'name DESC'; 
                break;
            case Amasty_Finder_Helper_Data::SORT_NUM_ASC :
                $order = new Zend_Db_Expr('ceil(name) ASC');
                break;
            case Amasty_Finder_Helper_Data::SORT_NUM_DESC :
                $order = new Zend_Db_Expr('ceil(name) DESC');
                break;
        }
       
        $collection->getSelect()->order($order);
        foreach ($collection as $option){
            $options[] = array(
                'value'    => $option->getValueId(), 
                'label'    => Mage::helper('amfinder')->__($option->getName()),
                'selected' => ($selected == $option->getValueId()),
            );
        }

        return $options;
    }

    public function getItems($parentId, $selected=0)
    {
        $items = array();

        $collection = Mage::getModel('amfinder/value')->getCollection()
            ->addFieldToFilter('parent_id', $parentId);

        if (!$this->getPos()){
            $collection->addFieldToFilter('dropdown_id', $this->getId());
        }
        switch ($this->getSort()) {
            case Amasty_Finder_Helper_Data::SORT_STRING_ASC :
                $order = 'name ASC';
                break;
            case Amasty_Finder_Helper_Data::SORT_STRING_DESC :
                $order = 'name DESC';
                break;
            case Amasty_Finder_Helper_Data::SORT_NUM_ASC :
                $order = new Zend_Db_Expr('ceil(name) ASC');
                break;
            case Amasty_Finder_Helper_Data::SORT_NUM_DESC :
                $order = new Zend_Db_Expr('ceil(name) DESC');
                break;
        }

        $collection->getSelect()->order($order);
        foreach ($collection as $option){
            $sku = Mage::getModel('amfinder/value')->getSkuByModelId($option['value_id']);
            if($sku){
                $item = '<p data-item-value="'.$option->getData('value_id').'" finder-id="' . $option->getData('dropdown_id') .'" class="amfinder-item last">';

                $product = Mage::getModel('catalog/product')->load( Mage::getModel('catalog/product')->getIdBySku($sku));
                $item .= '<img data-item-value="'.$option->getData('value_id').'" finder-id="' . $option->getData('dropdown_id') .'" src="'.$product->getImageUrl().'" alt="" /></p>';
            }
            else{
                $item = '<p data-item-value="'.$option->getData('value_id').'" finder-id="' . $option->getData('dropdown_id') .'" class="amfinder-item">';
                $item .= '<img data-item-value="'.$option->getData('value_id').'" finder-id="' . $option->getData('dropdown_id') .'" src="'.Mage::getUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'amfinder/'.strtolower($this->load($option->getData('dropdown_id'))->getName()). '/' . strtolower($option->getName()).'.jpg" alt="" /></p>';
            }
            $item .= '<label data-item-value="'.$option->getData('value_id').'" finder-id="' . $option->getData('dropdown_id') .'">' . $option->getName() . '</label>';;
            $items[] = $item;
        }

        return $items;
    }
}