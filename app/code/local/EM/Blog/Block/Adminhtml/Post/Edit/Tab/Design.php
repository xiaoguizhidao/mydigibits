<?php
class EM_Blog_Block_Adminhtml_Post_Edit_Tab_Design extends EM_Blog_Block_Adminhtml_Element_Form
{
	protected $_post;
	
	public function __construct()
    {
        parent::__construct();
        $this->setShowGlobalIcon(true);
    }

    public function getPost()
    {
        return Mage::registry('post_data');
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $form = new Varien_Data_Form();
        $form->setDataObject($this->getPost());
		$this->setForm($form);
		$fieldset = $form->addFieldset('design_fieldset', array(
            'legend' => Mage::helper('cms')->__('Custom Design'),
            'class'  => 'fieldset-wide',
            'disabled'  => $isElementDisabled
        ));
		$post = $this->getPost();
		$group = array(
			'custom_design','custom_design_from','custom_design_to','custom_layout','custom_layout_update_xml'
		);
		$attributes = $post->getAttributes($group);
		
		$this->_setFieldset($attributes,$fieldset);
		
		$form->setFieldNameSuffix('post');
        if ( Mage::getSingleton('adminhtml/session')->getPostData() )
		{
		  $form->setValues(Mage::getSingleton('adminhtml/session')->getPostData());
		  Mage::getSingleton('adminhtml/session')->setPostData(null);
		} elseif ( Mage::registry('post_data') ) {
		  $form->setValues(Mage::registry('post_data')->getData());
		}
        
        $this->setForm($form);
    }
}
