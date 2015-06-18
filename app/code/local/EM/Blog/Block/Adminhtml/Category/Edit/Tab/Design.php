<?php
class EM_Blog_Block_Adminhtml_Category_Edit_Tab_Design extends EM_Blog_Block_Adminhtml_Element_Form
{
	
	public function __construct()
    {
        parent::__construct();
        $this->setShowGlobalIcon(true);
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $form = new Varien_Data_Form();
        $form->setDataObject(Mage::registry('current_category'));
		$this->setForm($form);
		$fieldset = $form->addFieldset('design', array(
            'legend' => Mage::helper('blog')->__('Custom Design'),
            'class'  => 'fieldset-wide'
        ));
		
		$group = array(
			'custom_apply_to_products','custom_design','custom_design_from','custom_design_to','custom_layout','custom_layout_update_xml'
		);
		if(Mage::registry('current_category')->getLevel() != 1){
			$group[] = 'custom_use_parent_settings';
			if(Mage::registry('current_category')->getData('custom_use_parent_settings'))
				Mage::register('disabled','1');
		}
		$attributes = $this->getCategory()->getAttributes($group);
		
		$this->_setFieldset($attributes,$fieldset);
		if(Mage::registry('disabled'))
			Mage::unregister('disabled');
        if ( Mage::getSingleton('adminhtml/session')->getCategoryData() )
		{
		  $form->setValues(Mage::getSingleton('adminhtml/session')->getCategoryData());
		  Mage::getSingleton('adminhtml/session')->getCategoryData(null);
		} elseif ( Mage::registry('current_category') ) {
		  $form->setValues(Mage::registry('current_category')->getData());
		}
		$form->addValues(Mage::registry('category')->getData());
        $form->setFieldNameSuffix('general');
        $this->setForm($form);
    }
	
	public function getCategory()
    {
        if (!$this->_category) {
            $this->_category = Mage::registry('category');
        }
        return $this->_category;
    }
}