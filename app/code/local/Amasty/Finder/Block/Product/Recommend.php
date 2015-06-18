<?php
class Amasty_Finder_Block_Product_Recommend extends Mage_Catalog_Block_Product_List
{
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $layer = $this->getLayer();
            /* @var $layer Mage_Catalog_Model_Layer */
            if ($this->getShowRootCategory()) {
                $this->setCategoryId(Mage::app()->getStore()->getRootCategoryId());
            }

            // if this is a product view page
            if (Mage::registry('product')) {
                // get collection of categories this product is associated with
                $categories = Mage::registry('product')->getCategoryCollection()
                    ->setPage(1, 1)
                    ->load();
                // if the product is associated with any category
                if ($categories->count()) {
                    // show products from this category
                    $this->setCategoryId(current($categories->getIterator()));
                }
            }

            $origCategory = null;

            $this->_productCollection = $layer->getProductCollection();

            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
        }

        return $this->_productCollection;
    }

    public function getCollection(){
        If(Mage::registry('current_product')){
            $_product = Mage::registry('current_product');
        }

        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToFilter('attribute_set_id', array('neq' => $_product->getAttributeSetId()));
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        $cardTypes = $_product->getData('card_type');
        if($cardTypes){
            $cardTypes = explode(",", $cardTypes);
            $collection->addAttributeToFilter('card_type', array('in' => $cardTypes));
        }

        $capacity = $this->getCapacity();

        if($_product->getData('capacity')){
            $collection->addAttributeToFilter('capacity', array('in' => $capacity));
        }

        $collection->addAttributeToSort();

        $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));

        return $collection;
    }

    public function getProduct(){
        return Mage::registry('current_product');
    }

    public function getCapacity(){
        $product = $this->getProduct();

        $pCapacity = $product->getAttributeText('capacity');

        $options = Mage::getModel('eav/config')->getAttribute('catalog_product', 'capacity');
        foreach($options->getSource()->getAllOptions(true, true) as $option){
            if($option['value']){
                if($option['label']{strlen($option['label'])-2} == 'T'){
                    $option['label'] = substr($option['label'], 0, strlen($option['label'])-2) * 1024;
                    $capacities[$option['label']] = $option;
                }
                if($option['label']{strlen($option['label'])-2} == 'G'){
                    $option['label'] = substr($option['label'], 0, strlen($option['label'])-2);
                    $capacities[$option['label']] = $option;
                }
                if($option['label']{strlen($option['label'])-2} == 'M'){
                    $option['label'] = substr($option['label'], 0, strlen($option['label'])-2) / 1024;
                    $capacities[$option['label']] = $option;
                }
            }
        }

        krsort($capacities);

        for($index = 0; $index < count($capacities); $index++){
            if(current($capacities)['label'] == substr($pCapacity, 0, strlen($pCapacity-2))){
                $_capacities[] = current($capacities)['value'];
                $_capacities[] = next($capacities)['value'];

                continue;
            }

            next($capacities);
        }

        return $_capacities;
    }
}