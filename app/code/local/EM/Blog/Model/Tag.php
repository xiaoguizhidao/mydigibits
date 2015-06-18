<?php
class EM_Blog_Model_Tag extends Mage_Core_Model_Abstract
{
	protected $maxQty = 0;
    protected $minQty = 0;
	
    public function _construct()
    {
        parent::_construct();
        $this->_init('blog/tag');
    }
    
    /*
		Filter Tags by name
		@param string $tagName
		@return EM_Blog_Model_Resource_Tag_Collection
	*/
    
    public function getTagsAjax($tagName)
    {
        return $this->getCollection()->addFieldToFilter('name',array('like'=>"%$tagName%"));
    }
	
	/*
		Get Url Instance
		@return EM_Blog_Model_Url
	*/
	protected function getUrlInstance(){
		return Mage::getSingleton('blog/url');
	}
	
	/*
		Save URL Rewrite
		@return EM_Blog_Model_Tag
	*/
	public function saveUrlRewrite(){
		$data = array(
			'category_id'	=>	NULL,
			'post_id'		=>	NULL,
			'tag_id'		=>	$this->getId(),
			'request_path'	=>	'tag/'.$this->getTagIdentifier().'.html'
		);

		$urlInstance = $this->getUrlInstance();
		$urlInstance->saveAndUpdateUrl($data,'tag_id');
		return $this;
	}
	
	public function setMaxQty($qty)
    {
        $this->maxQty = $qty;
        return $this;
    }
    
    public function setMinQty($qty)
    {
        $this->minQty = $qty;
        return $this;
    }
    
    public function getMaxQty()//lay ra so luong bai post cua tag co nhieu bai post nhat
    {
        return $this->maxQty;
    }
    
    public function getMinQty()//lay ra so luong bai post cua tag co it bai viet nhat
    {
        return $this->minQty;
    }
    
    public function getTagCloud()
    {	
		$tagCloud = $this->getResource()->getTagCloud();
         if(count($tagCloud))
         {
            $this->setMaxQty($tagCloud[0]['qty']);
            $this->setMinQty($tagCloud[count($tagCloud)-1]['qty']);
         }
         return $tagCloud;
         
    }
	
	public function getPostCollection($order,$dir){
		$collection = Mage::getModel('blog/post')->getCollection()->setStoreId(Mage::app()->getStore()->getId())
						->addAttributeToFilter('status',1)
						->addAttributeToSelect('*');
		$collection->getSelect()
			->join(
			array('post_tag'=>$this->getResource()->getTable('blog/tag_post')),
			'post_tag.tag_id='.$this->getId().'
			 AND post_tag.post_id=e.entity_id',
			array()
		 );
		if((!empty($order)) && $order != 'position')
			$collection->addAttributeToSort($order,$dir);
		else
			$collection->addAttributeToSort('created_at',$dir);
		return $collection;	
	}
	
	/**
     * Returns array with dates for custom design
     *
     * @return array
     */
    public function getCustomDesignDate()
    {
        $result = array();
        $result['from'] = $this->getData('custom_design_from');
        $result['to'] = $this->getData('custom_design_to');

        return $result;
    }
}
