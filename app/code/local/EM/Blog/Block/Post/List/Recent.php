<?php
class EM_Blog_Block_Post_List_Recent extends Mage_Core_Block_Template
{
	protected $_recentCollection = null;
    public function getRecentPost()
    {
		if(!$this->_recentCollection){
			$storeId = Mage::app()->getStore()->getId();
			$collection = Mage::getModel('blog/post')->getCollection()
					->setStoreId($storeId)	
					->addAttributeToSelect('*')
					->addAttributeToFilter('status',1)
					->addAttributeToSort('created_at','DESC');
			$collection->getSelect()
                   ->limit((int)Mage::getStoreConfig('blog/info/limit_recent_post'));
			$this->_recentCollection = $collection;	   
		}
        
        return $this->_recentCollection;
    }

}

