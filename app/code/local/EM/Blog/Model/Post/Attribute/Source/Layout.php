<?php
class EM_Blog_Model_Post_Attribute_Source_Layout extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	public function getAllOptions(){
		return Mage::getSingleton('page/source_layout')->toOptionArray(true);
	}
}