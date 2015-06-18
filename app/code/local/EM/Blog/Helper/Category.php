<?php
class EM_Blog_Helper_Category extends Mage_Core_Helper_Abstract {
	public function getRootCategory(){
		if(!Mage::registry('blog_root_category')){
			$rootCategory = Mage::getSingleton('blog/category')->getCollection()->addAttributeToFilter('level',1)->getFirstItem();
			Mage::register('blog_root_category',$rootCategory);
		}
		return Mage::registry('blog_root_category');
	}
	
	/**
     * Check if a category can be shown
     *
     * @param  EM_Blog_Model_Category|int $category
     * @return boolean
     */
    public function canShow($category)
    {
        if (is_int($category)) {
            $category = Mage::getModel('blog/category')->load($category);
        }

        if (!$category->getId()) {
            return false;
        }

        if (!$category->getIsActive()) {
            return false;
        }
        if (!$category->isInRootCategoryList()) {
            return false;
        }

        return true;
    }
}