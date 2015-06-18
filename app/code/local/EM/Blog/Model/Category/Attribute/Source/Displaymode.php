<?php
class EM_Blog_Model_Category_Attribute_Source_Displaymode extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	public function getAllOptions(){
		return array(
				array(
					'value'     => 0,
					'label'     => Mage::helper('blog')->__('Articles Only'),
				),

				array(
					'value'     => 1,
					'label'     => Mage::helper('blog')->__('Static block only'),
				),
              
				array(
					'value'     => 2,
					'label'     => Mage::helper('blog')->__('Articles and Static block'),
				)
        );
	}
}