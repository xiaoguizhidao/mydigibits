<?php
class EM_Blog_Block_Comment_Recent extends Mage_Core_Block_Template
{   
    public function getCommentCollection()     
    { 
    	$limit = Mage::getStoreConfig('blog/info/limit_comment_recent');
        $con = 1 - Mage::getStoreConfig('blog/info/show_comment_pending');
		$collection = Mage::getModel('blog/comment')->getCollection()
						->addFieldToFilter('status_comment',array('gt'=>$con));
        $collection->getSelect()->limit($limit);
    	$collection ->setOrder('time', 'desc'); 
		return $collection;
    }

    public function getTitleComment($text, $length) {
       $length = abs((int)$length);
       if(strlen($text) > $length) {
          $text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $text);
       }
       return($text);
    }

}