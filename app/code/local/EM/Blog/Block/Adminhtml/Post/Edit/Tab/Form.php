<?php
class EM_Blog_Block_Adminhtml_Post_Edit_Tab_Form extends EM_Blog_Block_Adminhtml_Element_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$post = $this->getPost();
		$form->setDataObject($post);
		$fieldset = $form->addFieldset('post_form', array('legend'=>Mage::helper('blog')->__('Post information')));
		
		$group = array(
			'title','post_identifier','image','author_id','post_on','status','allow_comment'
		);
		$attributes = $post->getAttributes($group);
		
		$this->_setFieldset($attributes,$fieldset);
		$fieldset->addField('store', 'hidden', array(
			  'name'    => 'store',
		  ));
		$fieldset->addField('entity_id', 'hidden', array(
			  'name'    => 'entity_id',
		  ));	
		$form->setFieldNameSuffix('post');
		if ( Mage::getSingleton('adminhtml/session')->getPostData() )
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getPostData());
			Mage::getSingleton('adminhtml/session')->setPostData(null);
		} elseif ( Mage::registry('post_data') ) {
			$form->setValues(Mage::registry('post_data')->getData());
		}
		return parent::_prepareForm();
	}
	
	protected function _getAdditionalElementTypes()
    {
        return array(
            'image' => Mage::getConfig()->getBlockClassName('blog/adminhtml_post_helper_image')
        );
    }
}