<?php
class EM_Blog_Model_Resource_Category extends EM_Blog_Model_Resource_Abstract
{
	/**
     * Category tree object
     *
     * @var Varien_Data_Tree_Db
     */
    protected $_tree;

    /**
     * Catalog products table name
     *
     * @var string
     */
    protected $_categoryPostTable;

    /**
     * Id of 'is_active' category attribute
     *
     * @var int
     */
    protected $_isActiveAttributeId      = null;

    /**
     * Store id
     *
     * @var int
     */
    protected $_storeId                  = null;

    /**
     * Class constructor
     *
     */
	 
	/**
     * Resource initialization
     */
    public function __construct()
    {
        $this->setType(EM_Blog_Model_Category::ENTITY);
        $this->setConnection('blog_read', 'blog_write');
		$this->_categoryPostTable = $this->getTable('blog/category_post');
    }
	
	/**
     * Set store Id
     *
     * @param integer $storeId
     * @return Mage_Catalog_Model_Resource_Category
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Return store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            return Mage::app()->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Retrieve category tree object
     *
     * @return Varien_Data_Tree_Db
     */
    protected function _getTree()
    {
        if (!$this->_tree) {
            $this->_tree = Mage::getResourceModel('blog/category_tree')
                ->load();
        }
        return $this->_tree;
    }

    /**
     * Process category data before delete
     * update children count for parent category
     * delete child categories
     *
     * @param Varien_Object $object
     * @return EM_Blog_Model_Resource_Category
     */
    protected function _beforeDelete(Varien_Object $object)
    {
        parent::_beforeDelete($object);

        /**
         * Update children count for all parent categories
         */
        $parentIds = $object->getParentIds();
        if ($parentIds) {
            $childDecrease = $object->getChildrenCount() + 1; // +1 is itself
            $data = array('children_count' => new Zend_Db_Expr('children_count - ' . $childDecrease));
            $where = array('entity_id IN(?)' => $parentIds);
            $this->_getWriteAdapter()->update( $this->getEntityTable(), $data, $where);
        }
        $this->deleteChildren($object);
        return $this;
    }

    /**
     * Delete children categories of specific category
     *
     * @param Varien_Object $object
     * @return EM_Blog_Model_Resource_Category
     */
    public function deleteChildren(Varien_Object $object)
    {
        $adapter = $this->_getWriteAdapter();
        $pathField = $adapter->quoteIdentifier('path');

        $select = $adapter->select()
            ->from($this->getEntityTable(), array('entity_id'))
            ->where($pathField . ' LIKE :c_path');

        $childrenIds = $adapter->fetchCol($select, array('c_path' => $object->getPath() . '/%'));

        if (!empty($childrenIds)) {
            $adapter->delete(
                $this->getEntityTable(),
                array('entity_id IN (?)' => $childrenIds)
            );
        }

        /**
         * Add deleted children ids to object
         * This data can be used in after delete event
         */
        $object->setDeletedChildrenIds($childrenIds);
        return $this;
    }

    /**
     * Process category data before saving
     * prepare path and increment children count for parent categories
     *
     * @param Varien_Object $object
     * @return EM_Blog_Model_Resource_Category
     */
    protected function _beforeSave(Varien_Object $object)
    {
        parent::_beforeSave($object);

        if (!$object->getChildrenCount()) {
            $object->setChildrenCount(0);
        }
        if ($object->getLevel() === null) {
            $object->setLevel(1);
        }

        if (!$object->getId()) {
            $object->setPosition($this->_getMaxPosition($object->getPath()) + 1);
            $path  = explode('/', $object->getPath());
            $level = count($path);
            $object->setLevel($level);
            if ($level) {
                $object->setParentId($path[$level - 1]);
            }
            $object->setPath($object->getPath() . '/');

            $toUpdateChild = explode('/',$object->getPath());

            $this->_getWriteAdapter()->update(
                $this->getEntityTable(),
                array('children_count'  => new Zend_Db_Expr('children_count+1')),
                array('entity_id IN(?)' => $toUpdateChild)
            );
        }
		else{
			$oldImage = $object->getImage();
			if(is_array($oldImage)){
				if(isset($oldImage['delete'])){
					$path = Mage::getBaseDir('media').DS.'emblog'.DS.'category'.DS;
					$nameImage = $oldImage['value'];
					/* Remove primary image */					
					if(is_file($path.$nameImage))
						  unlink($path.$nameImage);
				}	
			}
		}
        return $this;
    }

    /**
     * Process category data after save category object
     * save related products ids and update path value
     *
     * @param Varien_Object $object
     * @return EM_Blog_Model_Resource_Category
     */
    protected function _afterSave(Varien_Object $object)
    {
        /**
         * Add identifier for new category
         */
        if (substr($object->getPath(), -1) == '/') {
            $object->setPath($object->getPath() . $object->getId());
            $this->_savePath($object);
        }

        $this->_saveCategoryPosts($object);
        return parent::_afterSave($object);
    }

	/**
     * Update path field
     *
     * @param EM_Blog_Model_Category $object
     * @return EM_Blog_Model_Resource_Category
     */
    protected function _savePath($object)
    {
        if ($object->getId()) {
            $this->_getWriteAdapter()->update(
                $this->getEntityTable(),
                array('path' => $object->getPath()),
                array('entity_id = ?' => $object->getId())
            );
        }
        return $this;
    }

    /**
     * Get maximum position of child categories by specific tree path
     *
     * @param string $path
     * @return int
     */
    protected function _getMaxPosition($path)
    {
        $adapter = $this->getReadConnection();
        $positionField = $adapter->quoteIdentifier('position');
        $level   = count(explode('/', $path));
        $bind = array(
            'c_level' => $level,
            'c_path'  => $path . '/%'
        );
        $select  = $adapter->select()
            ->from($this->getTable('blog/category'), 'MAX(' . $positionField . ')')
            ->where($adapter->quoteIdentifier('path') . ' LIKE :c_path')
            ->where($adapter->quoteIdentifier('level') . ' = :c_level');

        $position = $adapter->fetchOne($select, $bind);
        if (!$position) {
            $position = 0;
        }
        return $position;
    }

    /**
     * Save category products relation
     *
     * @param EM_Blog_Model_Category $category
     * @return EM_Blog_Model_Resource_Category
     */
    protected function _saveCategoryPosts($category)
    {
        $category->setIsChangedProductList(false);
        $id = $category->getId();
        /**
         * new category-product relationships
         */
        $posts = $category->getPostedPosts();

        /**
         * Example re-save category
         */
        if ($posts === null) {
            return $this;
        }

        /**
         * old category-product relationships
         */
        $oldPosts = $category->getPostsPosition();

        $insert = array_diff_key($posts, $oldPosts);
        $delete = array_diff_key($oldPosts, $posts);

        /**
         * Find product ids which are presented in both arrays
         * and saved before (check $oldPosts array)
         */
        $update = array_intersect_key($posts, $oldPosts);
        $update = array_diff_assoc($update, $oldPosts);

        $adapter = $this->_getWriteAdapter();

        /**
         * Delete posts from category
         */
        if (!empty($delete)) {
            $cond = array(
                'post_id IN(?)' => array_keys($delete),
                'category_id=?' => $id
            );
            $adapter->delete($this->_categoryPostTable, $cond);
        }

        /**
         * Add products to category
         */
        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $postId => $position) {
                $data[] = array(
                    'category_id' => (int)$id,
                    'post_id'  => (int)$postId,
                    'position'    => (int)$position
                );
            }
            $adapter->insertMultiple($this->_categoryPostTable, $data);//echo 'save category aaaaaaaaa';
        }

        /**
         * Update product positions in category
         */
        if (!empty($update)) {
            foreach ($update as $productId => $position) {
                $where = array(
                    'category_id = ?'=> (int)$id,
                    'post_id = ?' => (int)$productId
                );
                $bind  = array('position' => (int)$position);
                $adapter->update($this->_categoryPostTable, $bind, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $postIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            /*Mage::dispatchEvent('catalog_category_change_products', array(
                'category'      => $category,
                'product_ids'   => $productIds
            ));*/
        }

        if (!empty($insert) || !empty($update) || !empty($delete) || $category->getIsAnchor()) {
            $category->setIsChangedProductList(true);

            /**
             * Setting affected products to category for third party engine index refresh
             */
            $postIds = array_keys($insert + $delete + $update);
            $category->setAffectedProductIds($postIds);
        }
		
		/* Remove is_parent = 0 if is_anchor = 0 */
		if(!$category->getIsAnchor())
			$this->removeNonAnchor($category);
        return $this;
    }
	
	/* 
		Remove relation category - product is anchor is 0
		@param EM_Blog_Model_Category
		@return EM_Blog_Model_Resource_Category
	*/
	protected function removeNonAnchor($category){
		$this->_getWriteAdapter()->delete(
            $this->getTable('blog/category_post_index'),
			array(
				$this->_getWriteAdapter()->quoteInto('category_id = ?', $category->getId()),
				$this->_getWriteAdapter()->quoteInto('is_parent = 0')
			)
        );
		return $this;
	}

    /**
     * Get positions of associated to category posts
     *
     * @param EM_Blog_Model_Category $category
     * @return array
     */
    public function getPostsPosition($category)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->_categoryPostTable, array('post_id', 'position'))
            ->where('category_id = :category_id');
        $bind = array('category_id' => (int)$category->getId());

        return $this->_getWriteAdapter()->fetchPairs($select, $bind);
    }

    /**
     * Get chlden categories count
     *
     * @param int $categoryId
     * @return int
     */
    public function getChildrenCount($categoryId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getEntityTable(), 'children_count')
            ->where('entity_id = :entity_id');
        $bind = array('entity_id' => $categoryId);

        return $this->_getReadAdapter()->fetchOne($select, $bind);
    }

    /**
     * Check if category id exist
     *
     * @param int $entityId
     * @return bool
     */
    public function checkId($entityId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getEntityTable(), 'entity_id')
            ->where('entity_id = :entity_id');
        $bind =  array('entity_id' => $entityId);

        return $this->_getReadAdapter()->fetchOne($select, $bind);
    }

    /**
     * Check array of category identifiers
     *
     * @param array $ids
     * @return array
     */
    public function verifyIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $select = $this->_getReadAdapter()->select()
            ->from($this->getEntityTable(), 'entity_id')
            ->where('entity_id IN(?)', $ids);

        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Get count of active/not active children categories
     *
     * @param EM_Blog_Model_Category $category
     * @param bool $isActiveFlag
     * @return int
     */
    public function getChildrenAmount($category, $isActiveFlag = true)
    {
        $storeId = Mage::app()->getStore()->getId();
        $attributeId = $this->_getIsActiveAttributeId();
        $table   = $this->getTable(array($this->getEntityTablePrefix(), 'int'));
        $adapter = $this->_getReadAdapter();
        $checkSql = $adapter->getCheckSql('c.value_id > 0', 'c.value', 'd.value');

        $bind = array(
            'attribute_id' => $attributeId,
            'store_id'     => $storeId,
            'active_flag'  => $isActiveFlag,
            'c_path'       => $category->getPath() . '/%'
        );
        $select = $adapter->select()
            ->from(array('m' => $this->getEntityTable()), array('COUNT(m.entity_id)'))
            ->joinLeft(
                array('d' => $table),
                'd.attribute_id = :attribute_id AND d.store_id = 0 AND d.entity_id = m.entity_id',
                array()
            )
            ->joinLeft(
                array('c' => $table),
                "c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.entity_id = m.entity_id",
                array()
            )
            ->where('m.path LIKE :c_path')
            ->where($checkSql . ' = :active_flag');

        return $this->_getReadAdapter()->fetchOne($select, $bind);
    }

    /**
     * Get "is_active" attribute identifier
     *
     * @return int
     */
    protected function _getIsActiveAttributeId()
    {
        if ($this->_isActiveAttributeId === null) {
            $bind = array(
                'catalog_category' => EM_Blog_Model_Category::ENTITY,
                'is_active'        => 'is_active',
            );
            $select = $this->_getReadAdapter()->select()
                ->from(array('a'=>$this->getTable('eav/attribute')), array('attribute_id'))
                ->join(array('t'=>$this->getTable('eav/entity_type')), 'a.entity_type_id = t.entity_type_id')
                ->where('entity_type_code = :catalog_category')
                ->where('attribute_code = :is_active');

            $this->_isActiveAttributeId = $this->_getReadAdapter()->fetchOne($select, $bind);
        }

        return $this->_isActiveAttributeId;
    }

    /**
     * Return entities where attribute value is
     *
     * @param array|int $entityIdsFilter
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param mixed $expectedValue
     * @return array
     */
    public function findWhereAttributeIs($entityIdsFilter, $attribute, $expectedValue)
    {
        $bind = array(
            'attribute_id' => $attribute->getId(),
            'value'        => $expectedValue
        );
        $select = $this->_getReadAdapter()->select()
            ->from($attribute->getBackend()->getTable(), array('entity_id'))
            ->where('attribute_id = :attribute_id')
            ->where('value = :value')
            ->where('entity_id IN(?)', $entityIdsFilter);

        return $this->_getReadAdapter()->fetchCol($select, $bind);
    }

    /**
     * Get products count in category
     *
     * @param EM_Blog_Model_Category $category
     * @return int
     */
    public function getProductCount($category)
    {
        $productTable = Mage::getSingleton('core/resource')->getTableName('blog/category_product');

        $select = $this->getReadConnection()->select()
            ->from(
                array('main_table' => $productTable),
                array(new Zend_Db_Expr('COUNT(main_table.post_id)'))
            )
            ->where('main_table.category_id = :category_id');

        $bind = array('category_id' => (int)$category->getId());
        $counts = $this->getReadConnection()->fetchOne($select, $bind);

        return intval($counts);
    }

    /**
     * Retrieve categories
     *
     * @param integer $parent
     * @param integer $recursionLevel
     * @param boolean|string $sorted
     * @param boolean $asCollection
     * @param boolean $toLoad
     * @return Varien_Data_Tree_Node_Collection|EM_Blog_Model_Resource_Category_Collection
     */
    public function getCategories($parent, $recursionLevel = 0, $sorted = false, $asCollection = false, $toLoad = true)
    {
        $tree = Mage::getResourceModel('blog/category_tree');
        /* @var $tree EM_Blog_Model_Resource_Category_Tree */
        $nodes = $tree->loadNode($parent)
            ->loadChildren($recursionLevel)
            ->getChildren();

        $tree->addCollectionData(null, $sorted, $parent, $toLoad, true);

        if ($asCollection) {
            return $tree->getCollection();
        }
        return $nodes;
    }

    /**
     * Return parent categories of category
     *
     * @param EM_Blog_Model_Category $category
     * @return array
     */
    public function getParentCategories($category)
    {
        $pathIds = array_reverse(explode(',', $category->getPathInStore()));
        $categories = Mage::getResourceModel('blog/category_collection')
            ->setStore(Mage::app()->getStore())
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('url_key')
            ->addFieldToFilter('entity_id', array('in' => $pathIds))
            ->addFieldToFilter('is_active', 1)
            ->load()
            ->getItems();
        return $categories;
    }

    /**
     * Return parent category of current category with own custom design settings
     *
     * @param EM_Blog_Model_Category $category
     * @return EM_Blog_Model_Category
     */
    public function getParentDesignCategory($category)
    {
        $pathIds = array_reverse($category->getPathIds());
        $collection = $category->getCollection()
            ->setStore(Mage::app()->getStore())
            ->addAttributeToSelect('custom_design')
            ->addAttributeToSelect('custom_design_from')
            ->addAttributeToSelect('custom_design_to')
            ->addAttributeToSelect('page_layout')
            ->addAttributeToSelect('custom_layout_update')
            ->addAttributeToSelect('custom_apply_to_products')
            ->addFieldToFilter('entity_id', array('in' => $pathIds))
            ->addFieldToFilter('custom_use_parent_settings', 0)
            ->addFieldToFilter('level', array('neq' => 0))
            ->setOrder('level', 'DESC')
            ->load();
        return $collection->getFirstItem();
    }


    /**
     * Return child categories
     *
     * @param EM_Blog_Model_Category $category
     * @return EM_Blog_Model_Resource_Category_Collection
     */
    public function getChildrenCategories($category)
    {
        $collection = $category->getCollection();
        /* @var $collection EM_Blog_Model_Resource_Category_Collection */
        $collection->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor')
            ->addAttributeToFilter('is_active', 1)
            ->addIdFilter($category->getChildren())
            ->setOrder('position', Varien_Db_Select::SQL_ASC)
            ->joinUrlRewrite()
            ->load();

        return $collection;
    }

    /**
     * Return children ids of category
     *
     * @param EM_Blog_Model_Category $category
     * @param boolean $recursive
     * @return array
     */
    public function getChildren($category, $recursive = true)
    {
        $attributeId  = (int)$this->_getIsActiveAttributeId();
        $backendTable = $this->getTable(array($this->getEntityTablePrefix(), 'int'));
        $adapter      = $this->_getReadAdapter();
        $checkSql     = $adapter->getCheckSql('c.value_id > 0', 'c.value', 'd.value');
		
        $bind = array(
            'attribute_id' => $attributeId,
            'store_id'     => $category->getStoreId(),
            'scope'        => 1,
            'c_path'       => $category->getPath() . '/%'
        );
        $select = $this->_getReadAdapter()->select()
            ->from(array('m' => $this->getEntityTable()), 'entity_id')
            ->joinLeft(
                array('d' => $backendTable),
                'd.attribute_id = :attribute_id AND d.store_id = 0 AND d.entity_id = m.entity_id',
                array()
            )
            ->joinLeft(
                array('c' => $backendTable),
                'c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.entity_id = m.entity_id',
                array()
            )
            ->where($checkSql . ' = :scope')
            ->where($adapter->quoteIdentifier('path') . ' LIKE :c_path');
        if (!$recursive) {
            $select->where($adapter->quoteIdentifier('level') . ' <= :c_level');
            $bind['c_level'] = $category->getLevel() + 1;
        }
        return $adapter->fetchCol($select, $bind);
    }

    /**
     * Return all children ids of category (with category id)
     *
     * @param EM_Blog_Model_Category $category
     * @return array
     */
    public function getAllChildren($category)
    {
        $children = $this->getChildren($category);
        $myId = array($category->getId());
        $children = array_merge($myId, $children);

        return $children;
    }

    /**
     * Check is category in list of store categories
     *
     * @param EM_Blog_Model_Category $category
     * @return boolean
     */
    public function isInRootCategoryList($category)
    {
        $rootCategoryId = Mage::helper('blog/category')->getRootCategory()->getId();

        return in_array($rootCategoryId, $category->getParentIds());
    }

    /**
     * Check category is forbidden to delete.
     * If category is root and assigned to store group return false
     *
     * @param integer $categoryId
     * @return boolean
     */
    public function isForbiddenToDelete($categoryId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('core/store_group'), array('group_id'))
            ->where('root_category_id = :root_category_id');
        $result = $this->_getReadAdapter()->fetchOne($select,  array('root_category_id' => $categoryId));

        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * Get category path value by its id
     *
     * @param int $categoryId
     * @return string
     */
    public function getCategoryPathById($categoryId)
    {
        $select = $this->getReadConnection()->select()
            ->from($this->getEntityTable(), array('path'))
            ->where('entity_id = :entity_id');
        $bind = array('entity_id' => (int)$categoryId);

        return $this->getReadConnection()->fetchOne($select, $bind);
    }

    /**
     * Move category to another parent node
     *
     * @param EM_Blog_Model_Category $category
     * @param EM_Blog_Model_Category $newParent
     * @param null|int $afterCategoryId
     * @return EM_Blog_Model_Resource_Category
     */
    public function changeParent(Mage_Catalog_Model_Category $category, Mage_Catalog_Model_Category $newParent,
        $afterCategoryId = null)
    {
        $childrenCount  = $this->getChildrenCount($category->getId()) + 1;
        $table          = $this->getEntityTable();
        $adapter        = $this->_getWriteAdapter();
        $levelFiled     = $adapter->quoteIdentifier('level');
        $pathField      = $adapter->quoteIdentifier('path');

        /**
         * Decrease children count for all old category parent categories
         */
        $adapter->update(
            $table,
            array('children_count' => new Zend_Db_Expr('children_count - ' . $childrenCount)),
            array('entity_id IN(?)' => $category->getParentIds())
        );

        /**
         * Increase children count for new category parents
         */
        $adapter->update(
            $table,
            array('children_count' => new Zend_Db_Expr('children_count + ' . $childrenCount)),
            array('entity_id IN(?)' => $newParent->getPathIds())
        );

        $position = $this->_processPositions($category, $newParent, $afterCategoryId);

        $newPath          = sprintf('%s/%s', $newParent->getPath(), $category->getId());
        $newLevel         = $newParent->getLevel() + 1;
        $levelDisposition = $newLevel - $category->getLevel();

        /**
         * Update children nodes path
         */
        $adapter->update(
            $table,
            array(
                'path' => new Zend_Db_Expr('REPLACE(' . $pathField . ','.
                    $adapter->quote($category->getPath() . '/'). ', '.$adapter->quote($newPath . '/').')'
                ),
                'level' => new Zend_Db_Expr( $levelFiled . ' + ' . $levelDisposition)
            ),
            array($pathField . ' LIKE ?' => $category->getPath() . '/%')
        );
        /**
         * Update moved category data
         */
        $data = array(
            'path'      => $newPath,
            'level'     => $newLevel,
            'position'  =>$position,
            'parent_id' =>$newParent->getId()
        );
        $adapter->update($table, $data, array('entity_id = ?' => $category->getId()));

        // Update category object to new data
        $category->addData($data);

        return $this;
    }

    /**
     * Process positions of old parent category children and new parent category children.
     * Get position for moved category
     *
     * @param EM_Blog_Model_Category $category
     * @param EM_Blog_Model_Category $newParent
     * @param null|int $afterCategoryId
     * @return int
     */
    protected function _processPositions($category, $newParent, $afterCategoryId)
    {
        $table          = $this->getEntityTable();
        $adapter        = $this->_getWriteAdapter();
        $positionField  = $adapter->quoteIdentifier('position');

        $bind = array(
            'position' => new Zend_Db_Expr($positionField . ' - 1')
        );
        $where = array(
            'parent_id = ?'         => $category->getParentId(),
            $positionField . ' > ?' => $category->getPosition()
        );
        $adapter->update($table, $bind, $where);

        /**
         * Prepare position value
         */
        if ($afterCategoryId) {
            $select = $adapter->select()
                ->from($table,'position')
                ->where('entity_id = :entity_id');
            $position = $adapter->fetchOne($select, array('entity_id' => $afterCategoryId));

            $bind = array(
                'position' => new Zend_Db_Expr($positionField . ' + 1')
            );
            $where = array(
                'parent_id = ?' => $newParent->getId(),
                $positionField . ' > ?' => $position
            );
            $adapter->update($table,$bind,$where);
        } elseif ($afterCategoryId !== null) {
            $position = 0;
            $bind = array(
                'position' => new Zend_Db_Expr($positionField . ' + 1')
            );
            $where = array(
                'parent_id = ?' => $newParent->getId(),
                $positionField . ' > ?' => $position
            );
            $adapter->update($table,$bind,$where);
        } else {
            $select = $adapter->select()
                ->from($table,array('position' => new Zend_Db_Expr('MIN(' . $positionField. ')')))
                ->where('parent_id = :parent_id');
            $position = $adapter->fetchOne($select, array('parent_id' => $newParent->getId()));
        }
        $position += 1;

        return $position;
    }
}
?>