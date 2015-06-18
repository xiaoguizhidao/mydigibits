<?php
class EM_Blog_Block_Adminhtml_Post_Edit_Tab_Description extends EM_Blog_Block_Adminhtml_Element_Form
{
  protected function _prepareForm()
  {
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$post = $this->getPost();
		$form->setDataObject($post);
		$fieldset = $form->addFieldset('post_form', array('legend'=>Mage::helper('blog')->__('Meta Information')));
		$group = array(
			'post_meta_keywords','post_meta_description'
		);
		$attributes = $post->getAttributes($group);
		
		$this->_setFieldset($attributes,$fieldset);
		$form->setFieldNameSuffix('post');
      if ( Mage::getSingleton('adminhtml/session')->getPostData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPostData());
          Mage::getSingleton('adminhtml/session')->setPostData(null);
      } elseif ( $post ) {
          $form->setValues($post->getData());
      }
      return parent::_prepareForm();
  }
}