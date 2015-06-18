<?php
class EM_Blog_Block_Adminhtml_Category_Edit_Tab_Display extends EM_Blog_Block_Adminhtml_Element_Form
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
		$fieldset = $form->addFieldset('display', array(
            'legend' => Mage::helper('cms')->__('Custom Design'),
            'class'  => 'fieldset-wide'
        ));
		
		$group = array(
			'display_mode','cms_block','is_anchor'
		);
		$attributes = $this->getCategory()->getAttributes($group);
		
		$this->_setFieldset($attributes,$fieldset);
		
		if ( Mage::getSingleton('adminhtml/session')->getCategoryData() )
		{
		  $form->setValues(Mage::getSingleton('adminhtml/session')->getCategoryData());
		  Mage::getSingleton('adminhtml/session')->getCategoryData(null);
		} elseif ( Mage::registry('current_category') ) {
		  $form->setValues(Mage::registry('current_category')->getCategoryData());
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
