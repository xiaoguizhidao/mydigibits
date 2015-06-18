<?php
class EM_Blog_Model_Post_Attribute_Source_Allowcomment extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'value' => 0,
                    'label' => Mage::helper('blog')->__('Login User'),
                ),
                array(
                    'value' => 1,
                    'label' => Mage::helper('blog')->__('Everyone'),
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('blog')->__('Stop'),
                )
            );
        }
        return $this->_options;
    }
}