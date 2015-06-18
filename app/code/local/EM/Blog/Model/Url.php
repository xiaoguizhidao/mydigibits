<?php
class EM_Blog_Model_Url extends Mage_Core_Model_Abstract
{
	public function _construct()
    {
        $this->_init('blog/url');
    }
	
	public function saveAndUpdateUrl($data,$filedFilter)//$flag de nhan biet la update hay cap nhat du lieu,$name= Tag hoac Post
    {
        $tmp = $this->getCollection()
                        ->addFieldToFilter($filedFilter,$data[$filedFilter])
                        ->getFirstItem();
        $this->setData($data)->setId($tmp->getId())->save();
    }
    
    /*public function getDataUrl($requestInfo)
    {
         $write = Mage::getSingleton('core/resource')->getConnection('core_write');
         $query = "select * from blog_url_rewrite where request_path = '$requestInfo' limit 1";
         return $write->fetchRow($query);
    }
    
    public function getDataByPostId($post_id)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $query = "select * from blog_url_rewrite where post_id = $post_id limit 1";
        return $write->fetchRow($query);
    }
    
    public function getDataByTagId($tag_id)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $query = "select * from blog_url_rewrite where tag_id = $tag_id limit 1";
        return $write->fetchRow($query);
    }*/

    public function validate($url,$field,$id = null)
    {
        $collection = $this->getCollection()->addFieldToFilter('request_path',$url);
		if($collection->count() == 1){
			if(!$id)
				return true;
			$object = $collection->getFirstItem();
			if($object->getData($field) == $id)
				return false;
		}
		return false;
    }

}