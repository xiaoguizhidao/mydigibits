<?php
class EM_Blog_Model_Resource_Post extends EM_Blog_Model_Resource_Abstract
{
	/**
     * Post to category linkage table
     *
     * @var string
     */
    protected $_productCategoryTable;
	
	/**
     * Post to tag linkage table
     *
     * @var string
     */
    protected $_postTagTable;
	
	/**
     * Resource initialization
     */
    public function __construct()
    {
        $this->setType(EM_Blog_Model_Post::ENTITY);
        $this->setConnection('blog_read', 'blog_write');
		$this->_productCategoryTable = $this->getTable('blog/category_post');
		$this->_postTagTable = $this->getTable('blog/tag_post');
    }
	
	/**
     * Process post data before save
     *
     * @param Varien_Object $object
     * @return EM_Blog_Model_Resource_Post
     */
    protected function _beforeSave(Varien_Object $object)
    {
        /**
         * Check if declared category ids in object data.
         */
        if ($object->hasCategoryIds()) {
            $categoryIds = Mage::getResourceSingleton('blog/category')->verifyIds(
                $object->getCategoryIds()
            );
            $object->setCategoryIds($categoryIds);
        }
		
		if($object->getId()){
			$oldImage = $object->getImage();
			// Remove Old Image
			if(is_array($oldImage)){
				if(isset($oldImage['delete'])){
					$path = Mage::getBaseDir('media').DS.'emblog'.DS.'post'.DS;
					$nameImage = $oldImage['value'];
					
					/* Remove primary image */				
					if(is_file($path.$nameImage))
						  unlink($path.$nameImage);
					
				
					/* Remove thumbnail image */
					$thumbnailWidth = Mage::getStoreConfig('blog/info/thumbnail_width');
					$thumbnailHeight = Mage::getStoreConfig('blog/info/thumbnail_height');
					if(is_file($path.'thumbnail'.DS."{$thumbnailWidth}x{$thumbnailHeight}".DS.$nameImage))
						  unlink($path.'thumbnail'.DS."{$thumbnailWidth}x{$thumbnailHeight}".DS.$nameImage);
					
					
					/* Remove thumbnail image at recent post blog */
					$thumbnailWidth = Mage::getStoreConfig('blog/info/recent_thumbnail_width');
					$thumbnailHeight = Mage::getStoreConfig('blog/info/recent_thumbnail_height');
					if(is_file($path.'thumbnail'.DS."{$thumbnailWidth}x{$thumbnailHeight}".DS.$nameImage))
						  unlink($path.'thumbnail'.DS."{$thumbnailWidth}x{$thumbnailHeight}".DS.$nameImage);
				}
			}
		}

        return parent::_beforeSave($object);
    }
	
	/**
     * Save data related with post
     *
     * @param Varien_Object $post
     * @return EM_Blog_Model_Resource_Post
     */
    protected function _afterSave(Varien_Object $post)
    {
        $this->_saveCategories($post);
		$this->saveUrlRewrite($post);
        $this->saveTags($post);
        return parent::_afterSave($post);
    }
	
	/*
		Save URL Rewrite
		@return EM_Blog_Model_Post
	*/
	public function saveUrlRewrite($post){
		$data = array(
			'category_id'	=>	NULL,
			'post_id'		=>	$post->getId(),
			'tag_id'		=>	NULL,
			'request_path'	=>	$post->getPostIdentifier().'.html'
		);

		$urlInstance = Mage::getModel('blog/url');
		$urlInstance->saveAndUpdateUrl($data,'post_id');
		return $this;
	}
	
	/**
     * Save post category relations
     *
     * @param Varien_Object $object
     * @return EM_Blog_Model_Resource_Post
     */
    protected function _saveCategories(Varien_Object $object)
    {
        /**
         * If category ids data is not declared we haven't do manipulations
         */
        if (!$object->hasCategoryIds()) {
            return $this;
        }
        $categoryIds = $object->getCategoryIds();
        $oldCategoryIds = $this->getCategoryIds($object);

        $object->setIsChangedCategories(false);

        $insert = array_diff($categoryIds, $oldCategoryIds);
        $delete = array_diff($oldCategoryIds, $categoryIds);
        $write = $this->_getWriteAdapter();
        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $categoryId) {
                if (empty($categoryId)) {
                    continue;
                }
                $data[] = array(
                    'category_id' => (int)$categoryId,
                    'post_id'  => (int)$object->getId(),
                    'position'    => 1
                );
            }
            if ($data) {
                $write->insertMultiple($this->_productCategoryTable, $data);
            }
        }

        if (!empty($delete)) {
            foreach ($delete as $categoryId) {
                $where = array(
                    'post_id = ?'  => (int)$object->getId(),
                    'category_id = ?' => (int)$categoryId,
                );

                $write->delete($this->_productCategoryTable, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $object->setAffectedCategoryIds(array_merge($insert, $delete));
            $object->setIsChangedCategories(true);
        }

        return $this;
    }
	
	/**
     * Retrieve post category identifiers
     *
     * @param EM_Blog_Model_Post $post
     * @return array
     */
    public function getCategoryIds($post)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->_productCategoryTable, 'category_id')
            ->where('post_id = ?', (int)$post->getId());

        return $adapter->fetchCol($select);
    }
	
	/*
		Save tags for post
		@param EM_Blog_Model_Post $post
		@return EM_Blog_Model_Resource_Post
	*/
	public function saveTags($post){
		if(!$post->hasTagIds()){
			$post->setTagIds(array());
		}
		$tagNames = Mage::app()->getRequest()->getParam('tags_name');
		$tagIds = $post->getTagIds();
		
        $oldTagIds = $this->getTagIds($post);
		$insert = array_diff($tagIds, $oldTagIds);
	
        $delete = array_diff($oldTagIds, $tagIds);
		$write = $this->_getWriteAdapter();
		if (!empty($insert)) {
            $data = array();
			
            foreach ($insert as $index => $tagId) {
			
            	if($tagId == 0){
					/* Insert New Tag */
					// Validate tag
					$url = Mage::helper('blog/post')->friendlyURL($tagNames[$index]);
					$urlInstance = Mage::getModel('blog/url');
					if($urlInstance->validate('tag/'.$url.'.html','tag_id') == false){ // Validate is valid
						$dataTag = array('name' => $tagNames[$index],'tag_identifier' => $url);
						$tagId = Mage::getModel('blog/tag')->setData($dataTag)->save()->saveUrlRewrite()->getId();	
					}
					else
						continue;
				}
                $data[] = array(
                    'tag_id' => (int)$tagId,
                    'post_id'  => (int)$post->getId()
                );
            }
            if ($data) {
                $write->insertMultiple($this->_postTagTable, $data);
            }
        }

        if (!empty($delete)) {
            foreach ($delete as $tagId) {
                $where = array(
                    'post_id = ?'  => (int)$post->getId(),
                    'tag_id = ?' => (int)$tagId,
                );

                $write->delete($this->_postTagTable, $where);
            }
        }
	}	
	
	/**
     * Retrieve post tags identifiers
     *
     * @param EM_Blog_Model_Post $post
     * @return array
     */
    public function getTagIds($post)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->_postTagTable, 'tag_id')
            ->where('post_id = ?', (int)$post->getId());

        return $adapter->fetchCol($select);
    }
}
?>