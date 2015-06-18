<?php

class EM_Blog_Block_Adminhtml_Post_Edit_Tab_Design extends Mage_Adminhtml_Block_Widget_Form
{
	protected $_post;
	
	public function getPost()
    {
        if (!$this->_post) {
            $this->_post = Mage::registry('post');
        }
        return $this->_post;
    }
		
	protected function _prepareForm()
	{
	  $form = new Varien_Data_Form();
	  //$this->setForm($form);
	 
	  //$formCat = new Mage_Adminhtml_Block_Catalog_Category_Tab_Design();
	  //$form->setDataObject($this->getPost());
	  $fieldset = $form->addFieldset('post_form', array('legend'=>Mage::helper('blog')->__('Post information')));
	 /*print_r($this->getDesignAttributes());exit;
	  $this->_setFieldset($this->getPost()->getDesignAttributes(), $fieldset);
	  $form->addValues($this->getPost()->getData());
	  $form->setFieldNameSuffix('general');
	  $this->setForm($form);*/
	  
		$fieldset->addField('show_per_page', 'text', array(
          'label'     => Mage::helper('blog')->__('Post Number on page'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'post_content_heading',
      ));
	  
	  if ( Mage::getSingleton('adminhtml/session')->getPostData() )
	  {
		  $form->setValues(Mage::getSingleton('adminhtml/session')->getPostData());
		  Mage::getSingleton('adminhtml/session')->setPostData(null);
	  } elseif ( Mage::registry('post_data') ) {
		  $form->setValues(Mage::registry('post_data')->getData());
	  }
	  
	  
	  
	  return parent::_prepareForm();
	}
	
	public function getDesignAttributes()
	{
		$result = array();
		$_designAttributes  = array(
        'custom_design',
        'custom_design_apply',
        'custom_design_from',
        'custom_design_to',
        'page_layout',
        'custom_layout_update'
		);
        foreach ($_designAttributes as $attrName) {
            $result[] = Mage::getSingleton('catalog/config')->getAttribute('catalog_category', $attrName);
			echo $attrName;
        }
		exit;
        //print_r($result);exit;
        return $result;
	}
}
