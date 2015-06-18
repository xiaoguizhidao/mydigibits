<?php

class EM_Blog_Block_Adminhtml_Tag_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('tag_form', array('legend'=>Mage::helper('blog')->__('Tag information')));
     
      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('blog')->__('Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name',
      ));
		
      $fieldset->addField('tag_identifier', 'text', array(
          'label'     => Mage::helper('blog')->__('Identifier'),
          'name'      => 'tag_identifier',
          /*'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('blog')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('blog')->__('Disabled'),
              ),
          ),*/
      ));
      
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('blog')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('blog')->__('disable'),
              ),

              array(
                  'value'     => 0,
                  'label'     => Mage::helper('blog')->__('enable'),
              ),
         ),
      ));
      
      if ( Mage::getSingleton('adminhtml/session')->getTagData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getTagData());
          Mage::getSingleton('adminhtml/session')->setTagData(null);
      } elseif ( Mage::registry('tag_data') ) {
          $form->setValues(Mage::registry('tag_data')->getData());
      }
      return parent::_prepareForm();
  }
}