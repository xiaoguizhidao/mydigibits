<?php

class EM_Blog_Block_Adminhtml_Comment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('comment_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('blog')->__('Comment Information'));
      
  }

  protected function _beforeToHtml()
  {
  	
      $this->addTab('comment', array(
          'label'     => Mage::helper('blog')->__('Comment information'),
          'title'     => Mage::helper('blog')->__('Comment information'),
          'content'   => $this->getLayout()->createBlock('blog/adminhtml_comment_edit_tab_form')->toHtml(),
      ));
    
      $this->addTab('content', array(
          'label'     => Mage::helper('blog')->__('Content'),
          'title'     => Mage::helper('blog')->__('Content'),
          'content'   => $this->getLayout()->createBlock('blog/adminhtml_comment_edit_tab_content')->toHtml(),
      ));
    
		return parent::_beforeToHtml();
  }
}