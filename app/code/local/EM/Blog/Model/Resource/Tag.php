<?php
class EM_Blog_Model_Resource_Tag extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {    
        // Note that the blog_id refers to the key field in your database table.
        $this->_init('blog/tag', 'id');
    }
	
	/*
		Get tags of post
		@param EM_Blog_Model_Post $post
		@return EM_Blog_Model_Resource_Tag_Collection
	*/
	public function getTagsByPost($post)
	{
		$collection = Mage::getResourceModel('blog/tag_collection');
		$collection->getSelect()
				->join(
					array('tag_post' => $this->getTable('blog/tag_post')),
					'main_table.id = tag_post.tag_id AND tag_post.post_id = '.$post->getId(),
					array('post_id')
				);
		return $collection;		
	}
	
	public function getTagCloud(){
		$adapter = $this->_getReadAdapter();
		$select = $adapter->select()
					->from(array('tag' => $this->getTable('blog/tag')),array('tag.id','url'=>'tag.tag_identifier','tag.name'))
					->join(
						array('tag_post' => $this->getTable('blog/tag_post')),
						'tag_post.tag_id=tag.id AND tag.status=0',
						array('qty' => 'count(tag_post.post_id)')
					)
					->group('tag.id')
					->order('count(tag_post.post_id) DESC');
					
		return $this->_getReadAdapter()->fetchAll($select);
	}
	
	
}