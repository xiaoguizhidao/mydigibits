<?php
class EM_Blog_Model_Resource_Attribute_Collection extends Mage_Eav_Model_Resource_Entity_Attribute_Collection
{
	/**
     * Resource model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('blog/attribute', 'eav/entity_attribute');
    }
}