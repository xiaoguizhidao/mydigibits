<?php
class EM_Blog_Model_Comment extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('blog/comment');
    }
	
	public function getPost(){
		if(!$this->hasData('post')){
			$post = Mage::getModel('blog/post')->setStoreId(Mage::app()->getStore()->getId())->load($this->getPostId());
			$this->setData('post',$post);
		}
		return $this->getData('post');
	}
    
	public function getTitlePost(){
		
		return $this->getPost()->getTitle();	
	}
}