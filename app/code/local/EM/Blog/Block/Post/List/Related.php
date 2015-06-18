<?php
class EM_Blog_Block_Post_List_Related extends Mage_Core_Block_Template
{
	 protected $_itemCollection;

    protected function _prepareData()
    {
        $post = Mage::registry('current_post');
        /* @var $post EM_Blog_Model_Post */

        $this->_itemCollection = $post->getRelatedProductCollection()
			->setStoreId(Mage::app()->getStore()->getId())
            ->addAttributeToSelect('*')
			->addAttributeToFilter('status',1)
            ->setPositionOrder()
        ;

        $this->_itemCollection->load();

        return $this;
    }

    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    public function getItems()
    {
        return $this->_itemCollection;
    }

}

