<?php
class EM_Blog_Block_Adminhtml_Tag extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_tag';
    $this->_blockGroup = 'blog';
    $this->_headerText = Mage::helper('blog')->__('Tag Manager');
    parent::__construct();
  }
}
