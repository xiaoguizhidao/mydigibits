<?php
class EM_Blog_Block_Adminhtml_Comment_Edit_Tab_Content extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
	{

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(
            Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
        );
        
        //$wysiwygConfig = Mage::getSingleton('blog/wysiwyg_config')->getConfig();
        //print_r($wysiwygConfig);die;
        
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('comment_form', array('legend'=>Mage::helper('blog')->__('Comment information')));
      $fieldset->addField('comment_content', 'textarea', array(
          'name'      => 'comment_content',
          'label'     => Mage::helper('blog')->__('Content'),
          'title'     => Mage::helper('blog')->__('Content'),
          'style'     => 'width:400px; height:300px;',
      	  'required'  => true,
      )); 
      if ( Mage::getSingleton('adminhtml/session')->getCommentData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getCommentData());
          Mage::getSingleton('adminhtml/session')->setCommentData(null);
      } elseif ( Mage::registry('comment_data') ) {
          $form->setValues(Mage::registry('comment_data')->getData());
      }
      return parent::_prepareForm();
	}
}