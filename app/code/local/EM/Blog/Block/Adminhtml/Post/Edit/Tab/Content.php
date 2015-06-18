<?php
class EM_Blog_Block_Adminhtml_Post_Edit_Tab_Content extends EM_Blog_Block_Adminhtml_Element_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$post = $this->getPost();
		$form->setDataObject($post);
		$fieldset = $form->addFieldset('post_form', array('legend'=>Mage::helper('blog')->__('Content'),'class'=>'fieldset-wide'));
		$group = array(
			'post_content_heading','post_intro','post_content'
		);
		$attributes = $post->getAttributes($group);
		
		$this->_setFieldset($attributes,$fieldset);
		$form->setFieldNameSuffix('post');
		if ( Mage::getSingleton('adminhtml/session')->getPostData() )
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getPostData());
			Mage::getSingleton('adminhtml/session')->setPostData(null);
		} elseif ( Mage::registry('post_data') ) {
			$form->setValues($post->getData());
		}
		//Mage::dispatchEvent('blog_adminhtml_post_edit_tab_content_prepare_form', array('form' => $form));
		return parent::_prepareForm();
	}
}