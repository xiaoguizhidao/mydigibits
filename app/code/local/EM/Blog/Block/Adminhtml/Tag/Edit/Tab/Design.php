<?php

class EM_Blog_Block_Adminhtml_Tag_Edit_Tab_Design extends Mage_Adminhtml_Block_Widget_Form
{
	//protected $_post;
	
	public function __construct()
    {
        parent::__construct();
        $this->setShowGlobalIcon(true);
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $form = new Varien_Data_Form();
        $form->setDataObject($this->getCategory());

        $designFieldset = $form->addFieldset('design_fieldset', array(
            'legend' => Mage::helper('blog')->__('Custom Design'),
            'class'  => 'fieldset-wide'
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(
            Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
        );
        
        $designFieldset->addField('custom_design', 'select', array(
            'name'      => 'custom_design',
            'label'     => Mage::helper('blog')->__('Custom Design'),
            'values'    => Mage::getModel('core/design_source_design')->getAllOptions()
        ));

        $designFieldset->addField('custom_design_from', 'date', array(
            'name'      => 'custom_design_from',
            'label'     => Mage::helper('blog')->__('Custom Design From'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    =>Mage::app()->getLocale()->getDateFormatWithLongYear()
        ));

        $designFieldset->addField('custom_design_to', 'date', array(
            'name'      => 'custom_design_to',
            'label'     => Mage::helper('blog')->__('Custom Design To'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    =>Mage::app()->getLocale()->getDateFormatWithLongYear()
        ));

        $designFieldset->addField('custom_layout', 'select', array(
            'name'      => 'custom_layout',
            'label'     => Mage::helper('blog')->__('Custom Layout'),
            'values'    => Mage::getSingleton('page/source_layout')->toOptionArray(true)
        ));

        $designFieldset->addField('custom_layout_update_xml', 'textarea', array(
            'name'      => 'custom_layout_update_xml',
            'label'     => Mage::helper('blog')->__('Custom Layout Update XML'),
            'style'     => 'height:24em;'
        ));
        
        if ( Mage::getSingleton('adminhtml/session')->getTagData() )
		{
		  $form->setValues(Mage::getSingleton('adminhtml/session')->getTagData());
		  Mage::getSingleton('adminhtml/session')->setTagData(null);
		} elseif ( Mage::registry('tag_data') ) {
		  $form->setValues(Mage::registry('tag_data')->getData());
		}
        
        $this->setForm($form);
    }
}
