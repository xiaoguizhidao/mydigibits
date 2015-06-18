<?php

class EM_Blog_Block_Adminhtml_Tag_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('tag_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('blog')->__('Tag Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('tag', array(
          'label'     => Mage::helper('blog')->__('Tag Infomation'),
          'title'     => Mage::helper('blog')->__('Tag Infomation'),
          'content'   => $this->getLayout()->createBlock('blog/adminhtml_tag_edit_tab_form')->toHtml(),
      ));
      
      $this->addTab('design', array(
          'label'     => Mage::helper('blog')->__('Design'),
          'title'     => Mage::helper('blog')->__('Design'),
          'content'   => $this->getLayout()->createBlock('blog/adminhtml_tag_edit_tab_Design')->toHtml(),
      ));
      
      /*$this->addTab('content', array(
          'label'     => Mage::helper('blog')->__('Content'),
          'title'     => Mage::helper('blog')->__('Content'),
          'content'   => $this->getLayout()->createBlock('blog/adminhtml_post_edit_tab_Content')->toHtml(),
      ));
      
      $this->addTab('tabs', array(
          'label'     => Mage::helper('blog')->__('Tags'),
          'title'     => Mage::helper('blog')->__('Tags'),
          'content'   => $this->getLayout()->createBlock('blog/adminhtml_post_edit_tab_Tag')->toHtml(),
      ));
	  
	  $this->addTab('design', array(
          'label'     => Mage::helper('blog')->__('Design'),
          'title'     => Mage::helper('blog')->__('Design'),
          'content'   => $this->getLayout()->createBlock('blog/adminhtml_post_edit_tab_Design')->toHtml(),
      ));
      /*$this->addTab('design', array(
          'label'     => Mage::helper('blog')->__('Design'),
          'title'     => Mage::helper('blog')->__('Design'),
          'content'   => $this->getLayout()->createBlock('blog/adminhtml_post_edit_tab_form')->toHtml(),
      ));
      
      $this->addTab('tag', array(
          'label'     => Mage::helper('blog')->__('Tags'),
          'title'     => Mage::helper('blog')->__('Tags'),
          'content'   => $this->getLayout()->createBlock('blog/adminhtml_post_edit_tab_form')->toHtml(),
      ));*/
     
      return parent::_beforeToHtml();
  }
}