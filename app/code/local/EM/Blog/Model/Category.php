<?php
class EM_Blog_Model_Category extends EM_Blog_Model_Abstract
{
	/**
	* Maps to the array key from Setup.php::getDefaultEntities()
	*/
    const ENTITY = 'blog_category';
	/**
     * Category display modes
     */
    const DM_PRODUCT            = 'PRODUCTS';
    const DM_PAGE               = 'PAGE';
    const DM_MIXED              = 'PRODUCTS_AND_PAGE';
    const TREE_ROOT_ID          = 1;
	/**
     * Model post prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'blog';

    /**
     * Name of the cat object
     *
     * @var string
     */
    protected $_eventObject = 'category';
	/**
     * Initialize cat model
     */
	 
	//const TREE_ROOT_ID          = 1;

    /**
     * Category display modes
     */

    const CACHE_TAG             = 'blog_category';
	
    function _construct()
    {
        $this->_init('blog/category');
    }
	
	
	
	/**
     * Return store id.
     *
     * If store id is underfined for category return current active store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->_getData('store_id');
        }
        return Mage::app()->getStore()->getId();
    }
	
	/**
     * Retrieve category tree model
     *
     * @return EM_Blog_Model_Resource_Category_Tree
     */
    public function getTreeModel()
    {
        return Mage::getResourceModel('blog/category_tree');
    }
	
	/**
     * Enter description here...
     *
     * @return EM_Blog_Model_Resource_Category_Tree
     */
    public function getTreeModelInstance()
    {
        if (is_null($this->_treeModel)) {
            $this->_treeModel = Mage::getResourceSingleton('blog/category_tree');
        }
        return $this->_treeModel;
    }
	
	/**
     * Move category
     *
     * @param   int $parentId new parent category id
     * @param   int $afterCategoryId category id after which we have put current category
     * @return  Mage_Catalog_Model_Category
     */
    public function move($parentId, $afterCategoryId)
    {
        /**
         * Validate new parent category id. (category model is used for backward
         * compatibility in event params)
         */
        $parent = Mage::getModel('blog/category')
            ->setStoreId($this->getStoreId())
            ->load($parentId);

        if (!$parent->getId()) {
            Mage::throwException(
                Mage::helper('blog')->__('Category move operation is not possible: the new parent category was not found.')
            );
        }

        if (!$this->getId()) {
            Mage::throwException(
                Mage::helper('blog')->__('Category move operation is not possible: the current category was not found.')
            );
        } elseif ($parent->getId() == $this->getId()) {
            Mage::throwException(
                Mage::helper('blog')->__('Category move operation is not possible: parent category is equal to child category.')
            );
        }

        /**
         * Setting affected category ids for third party engine index refresh
        */
        $this->setMovedCategoryId($this->getId());

        $eventParams = array(
            $this->_eventObject => $this,
            'parent'        => $parent,
            'category_id'   => $this->getId(),
            'prev_parent_id'=> $this->getParentId(),
            'parent_id'     => $parentId
        );
        $moveComplete = false;

        $this->_getResource()->beginTransaction();
        try {
            /**
             * catalog_category_tree_move_before and catalog_category_tree_move_after
             * events declared for backward compatibility
             */
            //Mage::dispatchEvent('catalog_category_tree_move_before', $eventParams);
            Mage::dispatchEvent($this->_eventPrefix.'_move_before', $eventParams);

            $this->getResource()->changeParent($this, $parent, $afterCategoryId);

            Mage::dispatchEvent($this->_eventPrefix.'_move_after', $eventParams);
            //Mage::dispatchEvent('catalog_category_tree_move_after', $eventParams);
            $this->_getResource()->commit();

            // Set data for indexer
            $this->setAffectedCategoryIds(array($this->getId(), $this->getParentId(), $parentId));

            $moveComplete = true;
        } catch (Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
        if ($moveComplete) {
            Mage::dispatchEvent('category_move', $eventParams);
            Mage::getSingleton('index/indexer')->processEntityAction(
                $this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
            );
            Mage::app()->cleanCache(array(self::CACHE_TAG));
        }

        return $this;
    }
	
	/**
     * Get parent category object
     *
     * @return EM_Blog_Model_Category
     */
    public function getParentCategory()
    {
        if (!$this->hasData('parent_category')) {
            $this->setData('parent_category', Mage::getModel('blog/category')->load($this->getParentId()));
        }
        return $this->_getData('parent_category');
    }
	
	/**
     * Get parent category identifier
     *
     * @return int
     */
    public function getParentId()
    {
        $parentIds = $this->getParentIds();
        return intval(array_pop($parentIds));
    }
	
	/**
     * Get all parent categories ids
     *
     * @return array
     */
    public function getParentIds()
    {
        return array_diff($this->getPathIds(), array($this->getId()));
    }
	
	/**
     * Get all children categories IDs
     *
     * @param boolean $asArray return result as array instead of comma-separated list of IDs
     * @return array|string
     */
    public function getAllChildren($asArray = false)
    {
        $children = $this->getResource()->getAllChildren($this);
        if ($asArray) {
            return $children;
        }
        else {
            return implode(',', $children);
        }

//        $this->getTreeModelInstance()->load();
//        $children = $this->getTreeModelInstance()->getChildren($this->getId());
//
//        $myId = array($this->getId());
//        if (is_array($children)) {
//            $children = array_merge($myId, $children);
//        }
//        else {
//            $children = $myId;
//        }
//        if ($asArray) {
//            return $children;
//        }
//        else {
//            return implode(',', $children);
//        }
    }
	
	/**
     * Retrieve children ids comma separated
     *
     * @return string
     */
    public function getChildren()
    {
        return implode(',', $this->getResource()->getChildren($this, false));
    }
	
	/**
     * Get array categories ids which are part of category path
     * Result array contain id of current category because it is part of the path
     *
     * @return array
     */
    public function getPathIds()
    {
        $ids = $this->getData('path_ids');
        if (is_null($ids)) {
            $ids = explode('/', $this->getPath());
            $this->setData('path_ids', $ids);
        }
        return $ids;
    }

    /**
     * Retrieve level
     *
     * @return int
     */
    public function getLevel()
    {
        if (!$this->hasLevel()) {
            return count(explode('/', $this->getPath())) - 1;
        }
        return $this->getData('level');
    }
	
	/**
     * Validate attribute values
     *
     * @throws Mage_Eav_Model_Entity_Attribute_Exception
     * @return bool|array
     */
    public function validate()
    {
        return $this->_getResource()->validate($this);
    }
	
	/**
     * Retrieve array of post id's for category
     *
     * array($postId => $position)
     *
     * @return array
     */
    public function getPostsPosition()
    {
        if (!$this->getId()) {
            return array();
        }

        $array = $this->getData('posts_position');
        if (is_null($array)) {
            $array = $this->getResource()->getPostsPosition($this);
            $this->setData('posts_position', $array);
        }
        return $array;
    }
	
	/**
     * Retrieve all post attributes
     *
     * @todo Use with Flat Resource
     * @return array
     */
    public function getAttributes($group = array())
    {
        $postAttributes = $this->getResource()
            ->loadAllAttributes($this)
            ->getSortedAttributes();
		$attributes = array();	
		if(count($group)){
			foreach ($postAttributes as $attribute) {
				if (in_array($attribute->getAttributeCode(),$group)) {
					$attributes[] = $attribute;
				}
			}
		}
		else
			$attributes = $postAttributes;
        return $attributes;
    }
	
	/**
     * Return children categories of current category
     *
     * @return array
     */
    public function getChildrenCategories()
    {
        return $this->getResource()->getChildrenCategories($this);
    }
	
	/*
		Return url path without '.html' of category
		@param Array $dataPath
		@return string
	*/
	
	public function getPathUrl($pathAvailable = ''){
		if($pathAvailable){
			return $pathAvailable.'/'.$this->getUrlKey();
		}
		$dataPath = explode('/',$this->getPath());
		if(count($dataPath) < 3)
			return '';
		unset($dataPath[0]);	
		unset($dataPath[1]);
		$urlData = array();
		foreach($dataPath as $catId){
			$urlData[] = Mage::getModel('blog/category')->load($catId)->getUrlKey();
		}
		return implode('/',$urlData);
	}

	public function saveUrlRewriteChildren($parentCategory,$urlPathParent){
		$childrenCategories = $parentCategory->getChildrenCategories();
		if(!$childrenCategories->count())
			return $this;
		
		foreach($childrenCategories as $category){
			$urlPath = $category->getPathUrl($urlPathParent);
			$data = array(
				'category_id'	=>	$category->getId(),
				'post_id'		=>	NULL,
				'tag_id'		=>	NULL,
				'request_path'	=>	$urlPath.'.html'
			);
			$this->getUrlInstance()->saveAndUpdateUrl($data,'category_id');
			$this->saveUrlRewriteChildren($category,$urlPath);	
		}
		return $this;
	}
	
	
	/*
		Save URL Rewrite
		@param boolean $childrenChange
		@return EM_Blog_Model_Category
	*/
	public function saveUrlRewrite($childrenChange = false){
		
		$urlPath = $this->getPathUrl();
		$data = array(
			'category_id'	=>	$this->getId(),
			'post_id'		=>	NULL,
			'tag_id'		=>	NULL,
			'request_path'	=>	$urlPath.'.html'
		);
		$urlInstance = $this->getUrlInstance();
		$urlInstance->saveAndUpdateUrl($data,'category_id');
		if($childrenChange){
			$this->saveUrlRewriteChildren($this,$urlPath);
		}
		return $this;
	}

	public function getUrl(){
		$urlInstance = $this->getUrlInstance();
		return Mage::getBaseUrl().'blog/'.$this->getUrlInstance()->getCollection()
				->addFieldToFilter('category_id',$this->getId())
				->getFirstItem()->getRequestPath();
	}
	
	/**
     * Get category posts collection
     *
     * @return Varien_Data_Collection_Db
     */
    public function getPostCollection($order,$dir = 'desc')
    {
        $collection = Mage::getModel('blog/post')->getCollection()
			->addAttributeToSelect('*');
		if((!empty($order)) && $order != 'position')
			$collection->addAttributeToSort($order,$dir);
        $collection->setStoreId($this->getStoreId())
			->addAttributeToFilter('status',1)
            ->addCategoryFilter($this);
		$collection->getSelect()->order('position '.$dir);
        return $collection;
    }
	
	/**
     * Check category is in Blog Root Category list
     *
     * @return bool
     */
    public function isInRootCategoryList()
    {
        return $this->getResource()->isInRootCategoryList($this);
    }
	
	/**
     * Init indexing process after category save
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _afterSave()
    {
        $result = parent::_afterSave();
        Mage::getSingleton('index/indexer')->processEntityAction(
            $this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
        );
        return $result;
    }
}
?>