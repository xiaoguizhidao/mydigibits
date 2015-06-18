<?php

class EM_Blog_Block_Adminhtml_Comment_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
 protected function _prepareForm()
  {

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(
            Mage_Core_Model_Locale::FORMAT_TYPE_LONG
        );
                    
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('comment_form', array('legend'=>Mage::helper('blog')->__('Comment information')));

      $fieldset->addField('time', 'date', array(
            'name'      => 'time',
            'label'     => Mage::helper('blog')->__('Time'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::app()->getLocale()->getDateFormatWithLongYear(),
            'disabled'  => false,
      		//'readonly'  => true,
        ));

      
     $fieldset->addField('username', 'text', array(
          'label'     => Mage::helper('blog')->__('User name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'username',
      ));
      
    $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('blog')->__('User email'),
          'class'     => 'required-entry validate-email',
          'required'  => true,
          //'rows'      => '1',
          //'style'     => 'height:5em;',
          'name'      => 'email',
      ));

      
      $fieldset->addField('status_comment', 'select', array(
          'label'     => Mage::helper('blog')->__('Status'),
          'name'      => 'status_comment',
          'values'    => array(
              array(
                  'value'     => 2,
                  'label'     => Mage::helper('blog')->__('Approved'),
              ),
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('blog')->__('Pending'),
              ),
              array(
                  'value'     => 0,
                  'label'     => Mage::helper('blog')->__('Disabled'),
              )
          ),
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getPostData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPostData());
          Mage::getSingleton('adminhtml/session')->setPostData(null);
      } elseif ( Mage::registry('comment_data') ) {
          $form->setValues(Mage::registry('comment_data')->getData());
      }
      return parent::_prepareForm();
  }
}