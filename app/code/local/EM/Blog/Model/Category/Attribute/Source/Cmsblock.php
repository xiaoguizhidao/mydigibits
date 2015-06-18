<?php
class EM_Blog_Model_Category_Attribute_Source_Cmsblock extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = Mage::getResourceModel('cms/block_collection')
                ->load()
                ->toOptionArray();
            array_unshift($this->_options, array('value'=>'', 'label'=>Mage::helper('catalog')->__('Please select a static block ...')));
        }
        return $this->_options;
    }
}