<?php
class EM_Blog_Block_Adminhtml_Post_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('post_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('blog')->__('Post Information'));
  }
  
  /**
     * Load Wysiwyg on demand and Prepare layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

  protected function _beforeToHtml()
  {
		$this->addTab('post', array(
			'label'     => Mage::helper('blog')->__('General Information'),
			'title'     => Mage::helper('blog')->__('General Information'),
			'content'   => $this->getLayout()->createBlock('blog/adminhtml_post_edit_tab_form')->toHtml(),
		));
      
		$this->addTab('content', array(
			'label'     => Mage::helper('blog')->__('Content'),
			'title'     => Mage::helper('blog')->__('Content'),
			'content'   => $this->getLayout()->createBlock('blog/adminhtml_post_edit_tab_content')->toHtml(),
		));

		$this->addTab('categories', array(
			'label'     => Mage::helper('blog')->__('Categories'),
			'url'       => $this->getUrl('*/*/categories', array('_current' => true)),
			'class'     => 'ajax',
		));

		$this->addTab('tabs', array(
			'label'     => Mage::helper('blog')->__('Tags'),
			'title'     => Mage::helper('blog')->__('Tags'),
			'content'   => $this->getLayout()->createBlock('blog/adminhtml_post_edit_tab_tag')->toHtml(),
		));
	  
		$this->addTab('design', array(
			'label'     => Mage::helper('blog')->__('Design'),
			'title'     => Mage::helper('blog')->__('Design'),
			'content'   => $this->getLayout()->createBlock('blog/adminhtml_post_edit_tab_design')->toHtml(),
		));
		
		$this->addTab('related', array(
			'label'     => Mage::helper('blog')->__('Related Posts'),
			'url'       => $this->getUrl('*/*/related', array('_current' => true)),
			'class'     => 'ajax',
		));
	  
		$this->addTab('description', array(
			'label'     => Mage::helper('blog')->__('Meta Information'),
			'title'     => Mage::helper('blog')->__('Meta Information'),
			'content'   => $this->getLayout()->createBlock('blog/adminhtml_post_edit_tab_description')->toHtml(),
		));
      
      /*$this->addTab('tag', array(
          'label'     => Mage::helper('blog')->__('Tags'),
          'title'     => Mage::helper('blog')->__('Tags'),
          'content'   => $this->getLayout()->createBlock('blog/adminhtml_post_edit_tab_form')->toHtml(),
      ));*/
     
      return parent::_beforeToHtml();
  }
}