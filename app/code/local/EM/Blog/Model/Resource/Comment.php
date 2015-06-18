<?php
class EM_Blog_Model_Resource_Comment extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {    
        // Note that the blog_id refers to the key field in your database table.
        $this->_init('blog/comment', 'id');
    }
}