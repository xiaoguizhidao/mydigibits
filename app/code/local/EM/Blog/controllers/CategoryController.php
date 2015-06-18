<?php
class EM_Blog_CategoryController extends Mage_Core_Controller_Front_Action
{
	/**
     * Initialize requested category object
     *
     * @return EM_Blog_Model_Category
     */
	public function _initCategory()
    {
        
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        if (!$categoryId) {
            return false;
        }

        $category = Mage::getModel('blog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($categoryId);
		
		if (!Mage::helper('blog/category')->canShow($category)) {
            return false;
        }
		Mage::register('current_cat', $category);	
        return $category;
    }
	
    public function indexAction()
    {
        $this->loadLayout();
        $title = $this->getLayout()->getBlock('head')->getTitle();
        $this->getLayout()->getBlock('head')->setTitle("$title ".Mage::getStoreConfig('blog/info/page_title'));
        $this->renderLayout();
    }
	
	/**
     * Recursively apply custom design settings to category if it's option
     * custom_use_parent_settings is setted to 1 while parent option is not
     *
     * @deprecated after 1.4.2.0-beta1, functionality moved to Mage_Catalog_Model_Design
     * @param EM_Blog_Model_Category $category
     *
     * @return EM_Blog_CategoryController
     */
    protected function _applyCustomDesignSettings($category)
    {
        if ($category->getCustomUseParentSettings() && $category->getLevel() > 1) {
            $parentCategory = $category->getParentCategory();
            if ($parentCategory && $parentCategory->getId()) {
                return $this->_applyCustomDesignSettings($parentCategory);
            }
        }

        $validityDate = $category->getCustomDesignDate();

        if (array_key_exists('from', $validityDate) &&
            array_key_exists('to', $validityDate) &&
            Mage::app()->getLocale()->isStoreDateInInterval(null, $validityDate['from'], $validityDate['to'])
        ) {
            Mage::helper('blog')->setTheme($category->getData('custom_design'), $category->getData('custom_layout_update_xml'), $category->getData('custom_layout'),$this);
        }
		else{
			$this->loadLayout();
		}

        return $this;
    }

	/**
     * Category view action
     */
    public function viewAction()
    {
		$category = $this->_initCategory();
        if($category){
			$this->_applyCustomDesignSettings($category);
			$title = $this->getLayout()->getBlock('head')->getTitle();
			$this->getLayout()->getBlock('head')->setTitle("$title category ".$category->getPageTitle());
			$this->getLayout()->getBlock('head')->setKeywords($category->getMetaKeywords());
			$this->getLayout()->getBlock('head')->setDescription($category->getMetaDescription());
			$this->renderLayout();
		}
        elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }
}
