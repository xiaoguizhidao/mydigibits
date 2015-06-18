<?php
class EM_Blog_Block_Adminhtml_Category_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('category_info_tabs');
        $this->setDestElementId('category_tab_content');
        $this->setTitle(Mage::helper('catalog')->__('Category Data'));
        $this->setTemplate('widget/tabshoriz.phtml');
    }
	/**
     * Prepare Layout Content
     *
     * @return Mage_Adminhtml_Block_Catalog_Category_Tabs
     */
    protected function _prepareLayout()
    {
		if(!Mage::registry('category_attributes'))
			Mage::register('category_attributes',$this->getCategory()->getAttributes());
		
		$this->addTab('general', array(
			'label'     => Mage::helper('blog')->__('General Information'),
			'title'     => Mage::helper('blog')->__('General Information'),
			'content'   => $this->getLayout()->createBlock('blog/adminhtml_category_edit_tab_general')->toHtml()
		));
		
		$this->addTab('display', array(
			'label'     => Mage::helper('blog')->__('Display Setting'),
			'title'     => Mage::helper('blog')->__('Display Setting'),
			'content'   => $this->getLayout()->createBlock('blog/adminhtml_category_edit_tab_display')->toHtml()
		));
		
		$this->addTab('design', array(
			'label'     => Mage::helper('blog')->__('Custom Design'),
			'title'     => Mage::helper('blog')->__('Custom Design'),
			'content'   => $this->getLayout()->createBlock('blog/adminhtml_category_edit_tab_design')->toHtml()
		));
        $this->addTab('products', array(
            'label'     => Mage::helper('blog')->__('Category Posts'),
            'content'   => $this->getLayout()->createBlock(
                'blog/adminhtml_category_edit_tab_post',
                'category.product.grid'
            )->toHtml()
        ));

        // dispatch event add custom tabs
        /*Mage::dispatchEvent('adminhtml_catalog_category_tabs', array(
            'tabs'  => $this
        ));*/

        /*$this->addTab('features', array(
            'label'     => Mage::helper('catalog')->__('Feature Products'),
            'content'   => 'Feature Products'
        ));        */
        return parent::_prepareLayout();
    }
	
	public function getCategory()
    {
        return Mage::registry('category');
    }
}