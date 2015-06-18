<?php
class EM_Blog_Model_Resource_Category_Indexer_Post extends Mage_Index_Model_Resource_Abstract
{
	const STORE_ROOT_ID = 2;
	/**
     * Category table
     *
     * @var string
     */
    protected $_categoryTable;

    /**
     * Category product table
     *
     * @var string
     */
    protected $_categoryProductTable;

    /**
     * Store table
     *
     * @var string
     */
    protected $_storeTable;

    /**
     * Array of info about stores
     *
     * @var array
     */
    protected $_storesInfo;

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('blog/category_post_index', 'category_id');
        $this->_categoryTable        = $this->getTable('blog/category');
        $this->_categoryProductTable = $this->getTable('blog/category_post');
        $this->_storeTable           = $this->getTable('core/store');
    }
	
	/**
     * Process product save.
     * Method is responsible for index support
     * when product was saved and assigned categories was changed.
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Catalog_Model_Resource_Category_Indexer_Product
     */
    public function blogPostSave(Mage_Index_Model_Event $event)
    {
        $postId = $event->getEntityPk();
        $data      = $event->getNewData();

        /**
         * Check if category ids were updated
         */
        if (!isset($data['category_ids'])) {
            return $this;
        }

        /**
         * Select relations to categories
         */
        $select = $this->_getWriteAdapter()->select()
            ->from(array('cp' => $this->_categoryProductTable), 'category_id')
            ->joinInner(array('ce' => $this->_categoryTable), 'ce.entity_id=cp.category_id', 'path')
            ->where('cp.post_id=:post_id');

        /**
         * Get information about product categories
         */
        $categories = $this->_getWriteAdapter()->fetchPairs($select, array('post_id' => $postId));

        $categoryIds = array();
        $allCategoryIds = array();

        foreach ($categories as $id=>$path) {
            $categoryIds[]  = $id;
            $allCategoryIds = array_merge($allCategoryIds, explode('/', $path));
        }
        $allCategoryIds = array_unique($allCategoryIds);
        $allCategoryIds = array_diff($allCategoryIds, $categoryIds);

        /**
         * Delete previous index data
         */
        $this->_getWriteAdapter()->delete(
            $this->getMainTable(),
            array('post_id = ?' => $postId)
        );

        $this->_refreshAnchorRelations($allCategoryIds, $postId);
        $this->_refreshDirectRelations($categoryIds, $postId);
        return $this;
    }
	
	/**
     * Process category index after category save
     *
     * @param Mage_Index_Model_Event $event
     */
    public function blogCategorySave(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();

        $checkRootCategories        = false;
        $processRootCategories      = false;
        $affectedRootCategoryIds    = array();
        $rootCategories             = $this->_getRootCategories();

        /**
         * Check if we have reindex category move results
         */
        if (isset($data['affected_category_ids'])) {
            $categoryIds = $data['affected_category_ids'];
            $checkRootCategories = true;
        } else if (isset($data['products_was_changed'])) {
            $categoryIds = array($event->getEntityPk());

            if (isset($rootCategories[$event->getEntityPk()])) {
                $processRootCategories = true;
                $affectedRootCategoryIds[] = $event->getEntityPk();
            }
        } else {
            return;
        }

        $select = $this->_getWriteAdapter()->select()
            ->from($this->_categoryTable, 'path')
            ->where('entity_id IN (?)', $categoryIds);
        $paths = $this->_getWriteAdapter()->fetchCol($select);
        $allCategoryIds = array();
        foreach ($paths as $path) {
            if ($checkRootCategories) {
                foreach ($rootCategories as $rootCategoryId => $rootCategoryPath) {
                    if (strpos($path, sprintf('%d/', $rootCategoryPath)) === 0 || $path == $rootCategoryPath) {
                        $affectedRootCategoryIds[$rootCategoryId] = $rootCategoryId;
                    }
                }
            }
            $allCategoryIds = array_merge($allCategoryIds, explode('/', $path));
        }
        $allCategoryIds = array_unique($allCategoryIds);

        if ($checkRootCategories && count($affectedRootCategoryIds) > 1) {
            $processRootCategories = true;
        }

        /**
         * retrieve anchor category id
         */
        $anchorInfo = $this->_getAnchorAttributeInfo();
        $bind = array(
            'attribute_id' => $anchorInfo['id'],
            'store_id'     => EM_Blog_Model_Abstract::DEFAULT_STORE_ID,
            'e_value'      => 1
        );
        $select = $this->_getReadAdapter()->select()
            ->distinct(true)
            ->from(array('ce' => $this->_categoryTable), array('entity_id'))
            ->joinInner(
                array('dca'=>$anchorInfo['table']),
                "dca.entity_id=ce.entity_id AND dca.attribute_id=:attribute_id AND dca.store_id=:store_id",
                array())
             ->where('dca.value=:e_value')
             ->where('ce.entity_id IN (?)', $allCategoryIds);
        $anchorIds = $this->_getWriteAdapter()->fetchCol($select, $bind);
        /**
         * delete only anchor id and category ids
         */
        $deleteCategoryIds = array_merge($anchorIds,$categoryIds);
		
        $this->_getWriteAdapter()->delete(
            $this->getMainTable(),
            $this->_getWriteAdapter()->quoteInto('category_id IN(?)', $deleteCategoryIds)
        );

        $directIds = array_diff($categoryIds, $anchorIds);
        if ($anchorIds) {
            $this->_refreshAnchorRelations($anchorIds);
        }
        if ($directIds) {
            $this->_refreshDirectRelations($directIds);
        }

        /**
         * Need to re-index affected root category ids when its are not anchor
         */
        if ($processRootCategories) {
            $reindexRootCategoryIds = array_diff($affectedRootCategoryIds, $anchorIds);
            if ($reindexRootCategoryIds) {
                $this->_refreshNotAnchorRootCategories($reindexRootCategoryIds);
            }
        }

    }
	
	/**
     * Get is_anchor category attribute information
     *
     * @return array array('id' => $id, 'table'=>$table)
     */
    protected function _getAnchorAttributeInfo()
    {
        $isAnchorAttribute = Mage::getSingleton('eav/config')
            ->getAttribute(EM_Blog_Model_Category::ENTITY, 'is_anchor');
        $info = array(
            'id'    => $isAnchorAttribute->getId() ,
            'table' => $isAnchorAttribute->getBackend()->getTable()
        );
        return $info;
    }
	
	/**
     * Rebuild index for anchor categories and associated to child categories products
     *
     * @param null | array $categoryIds
     * @param null | array $productIds
     * @return Mage_Catalog_Model_Resource_Category_Indexer_Product
     */
    protected function _refreshAnchorRelations($categoryIds = null, $productIds = null)
    {
        if (!$categoryIds && !$productIds) {
            return $this;
        }

        $anchorInfo     = $this->_getAnchorAttributeInfo();
        //$visibilityInfo = $this->_getVisibilityAttributeInfo();
        $statusInfo     = $this->_getStatusAttributeInfo();

        /**
         * Insert anchor categories relations
         */
        $adapter = $this->_getReadAdapter();
        $isParent = $adapter->getCheckSql('MIN(cp.category_id)=ce.entity_id', 1, 0);
        $position = 'MIN('.
            $adapter->getCheckSql(
                'cp.category_id = ce.entity_id',
                'cp.position',
                '(cc.position + 1) * ('.$adapter->quoteIdentifier('cc.level').' + 1) * 10000 + cp.position'
            )
        .')';

        $select = $adapter->select()
            ->distinct(true)
            ->from(array('ce' => $this->_categoryTable), array('category_id'=>'entity_id'));
		
            $select->joinInner(
                array('cc' => $this->_categoryTable),
                $adapter->quoteIdentifier('cc.path') .
                ' LIKE ('.$adapter->getConcatSql(array($adapter->quoteIdentifier('ce.path'),$adapter->quote('/%'))).')'
                . ' OR cc.entity_id=ce.entity_id'
                , array()
            );
		
            $select->joinInner(
                array('cp' => $this->_categoryProductTable),
                'cp.category_id=cc.entity_id',
                array('cp.post_id', 'position' => $position, 'is_parent' => $isParent/*,'direct_category_id'=>'cp.category_id'*/)
            )
            ->joinInner(array('rc' => $this->_categoryTable), 'rc.entity_id='.self::STORE_ROOT_ID, array())
            ->joinLeft(
                array('dca'=>$anchorInfo['table']),
                "dca.entity_id=ce.entity_id AND dca.attribute_id={$anchorInfo['id']} AND dca.store_id=0",
                array())
            /*->joinLeft(
                array('sca'=>$anchorInfo['table']),
                "sca.entity_id=ce.entity_id AND sca.attribute_id={$anchorInfo['id']} AND sca.store_id=".Mage::app()->getRequest()->getParam('store',0),
                array())
            /*->joinLeft(
                array('dv'=>$visibilityInfo['table']),
                "dv.entity_id=pw.post_id AND dv.attribute_id={$visibilityInfo['id']} AND dv.store_id=0",
                array())
            ->joinLeft(
                array('sv'=>$visibilityInfo['table']),
                "sv.entity_id=pw.post_id AND sv.attribute_id={$visibilityInfo['id']} AND sv.store_id=s.store_id",
                array('visibility' => $adapter->getCheckSql(
                    'MIN(sv.value_id) IS NOT NULL',
                    'MIN(sv.value)', 'MIN(dv.value)'
                ))
            )*/
            ->joinLeft(
                array('ds'=>$statusInfo['table']),
                "ds.attribute_id={$statusInfo['id']} AND ds.store_id=0",
                array())
            ->joinLeft(
                array('ss'=>$statusInfo['table']),
                "ss.attribute_id={$statusInfo['id']} AND ss.store_id=".Mage::app()->getRequest()->getParam('store',0),
                array())
            /**
             * Condition for anchor or root category (all products should be assigned to root)
             */
            ->where('('.
                $adapter->quoteIdentifier('ce.path') . ' LIKE ' .
                $adapter->getConcatSql(array($adapter->quoteIdentifier('rc.path'), $adapter->quote('/%'))) . ' AND ' .
                $adapter->quoteIdentifier('dca.value') . '=1) OR ce.entity_id=rc.entity_id'
            )
            ->where(
                $adapter->getCheckSql('ss.value_id IS NOT NULL', 'ss.value', 'ds.value') . '=?',
                EM_Blog_Model_Post_Status::STATUS_ENABLED
            )
            ->group(array('ce.entity_id', 'cp.post_id'));
        if ($categoryIds) {
            $select->where('ce.entity_id IN (?)', $categoryIds);
        }
        if ($productIds) {
            $select->where('cp.post_id IN(?)', $productIds);
        }
		
//echo get_class($select);
        $sql = $select->insertFromSelect($this->getMainTable());
		//echo $sql;exit;
        $this->_getWriteAdapter()->query($sql);
        return $this;
    }
	
	/**
     * Rebuild index for direct associations categories and products
     *
     * @param null|array $categoryIds
     * @param null|array $productIds
     * @return Mage_Catalog_Model_Resource_Category_Indexer_Product
     */
    protected function _refreshDirectRelations($categoryIds = null, $productIds = null)
    {
        if (!$categoryIds && !$productIds) {
            return $this;
        }

        //$visibilityInfo = $this->_getVisibilityAttributeInfo();
        $statusInfo     = $this->_getStatusAttributeInfo();
        $adapter = $this->_getWriteAdapter();
        /**
         * Insert direct relations
         * post_ids (enabled filter) X category_ids X store_ids
         * Validate store root category
         */
        $isParent = new Zend_Db_Expr('1');
        $select = $adapter->select()
            ->from(array('cp' => $this->_categoryProductTable),
                array('category_id', 'post_id', 'position', $isParent))
            ->joinInner(array('rc'  => $this->_categoryTable), 'rc.entity_id='.self::STORE_ROOT_ID, array())
            ->joinInner(
                array('ce'=>$this->_categoryTable),
                'ce.entity_id=cp.category_id AND ('.
                $adapter->quoteIdentifier('ce.path') . ' LIKE ' .
                $adapter->getConcatSql(array($adapter->quoteIdentifier('rc.path') , $adapter->quote('/%'))) .
                ' OR ce.entity_id=rc.entity_id)',
                array())
           /* ->joinLeft(
                array('dv'=>$visibilityInfo['table']),
                $adapter->quoteInto(
                    "dv.entity_id=cp.post_id AND dv.attribute_id=? AND dv.store_id=0",
                    $visibilityInfo['id']),
                array()
            )
            ->joinLeft(
                array('sv'=>$visibilityInfo['table']),
                $adapter->quoteInto(
                    "sv.entity_id=cp.post_id AND sv.attribute_id=? AND sv.store_id=s.store_id",
                    $visibilityInfo['id']),
                array('visibility' => $adapter->getCheckSql('sv.value_id IS NOT NULL',
                    $adapter->quoteIdentifier('sv.value'),
                    $adapter->quoteIdentifier('dv.value')
                ))
            )*/
            ->joinLeft(
                array('ds'=>$statusInfo['table']),
                "ds.entity_id=cp.post_id AND ds.attribute_id={$statusInfo['id']} AND ds.store_id=0",
                array())
            ->joinLeft(
                array('ss'=>$statusInfo['table']),
                "ss.entity_id=cp.post_id AND ss.attribute_id={$statusInfo['id']} AND ss.store_id=".Mage::app()->getRequest()->getParam('store',0),
                array())
            ->where(
                $adapter->getCheckSql('ss.value_id IS NOT NULL',
                    $adapter->quoteIdentifier('ss.value'),
                    $adapter->quoteIdentifier('ds.value')
                ) . ' = ?',
                EM_Blog_Model_Post_Status::STATUS_ENABLED
            );
        if ($categoryIds) {
            $select->where('cp.category_id IN (?)', $categoryIds);
        }
        if ($productIds) {
            $select->where('cp.post_id IN(?)', $productIds);
        }
        $sql = $select->insertFromSelect(
            $this->getMainTable(),
            array('category_id', 'post_id', 'position', 'is_parent'/*, 'store_id'/*, 'visibility'*/),
            true
        );
        $adapter->query($sql);
        return $this;
    }
	
	/**
     * Reindex not anchor root categories
     *
     * @param array $categoryIds
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Indexer_Product
     */
    protected function _refreshNotAnchorRootCategories(array $categoryIds = null)
    {
        if (empty($categoryIds)) {
            return $this;
        }

        $adapter = $this->_getWriteAdapter();

        // remove anchor relations
        $where = array(
            'category_id IN(?)' => $categoryIds,
            'is_parent=?'       => 0
        );
        $adapter->delete($this->getMainTable(), $where);

        $stores = $this->_getStoresInfo();
        /**
         * Build index for each store
         */
        foreach ($stores as $storeData) {
            $storeId    = $storeData['store_id'];
            $rootPath   = $storeData['root_path'];
            $rootId     = $storeData['root_id'];
            if (!in_array($rootId, $categoryIds)) {
                continue;
            }

            $select = $adapter->select()
                ->distinct(true)
                ->from(array('cc' => $this->getTable('blog/category')), null)
                ->join(
                    array('i' => $this->getMainTable()),
                    'i.category_id = cc.entity_id',
                    array())
                ->where('cc.path LIKE ?', $rootPath . '/%')
                ->where('ie.category_id IS NULL')
                ->columns(array(
                    'category_id'   => new Zend_Db_Expr($rootId),
                    'post_id'    	=> 'i.post_id',
                    'position'      => new Zend_Db_Expr('0'),
                    'is_parent'     => new Zend_Db_Expr('0')
                ));
            $query = $select->insertFromSelect($this->getMainTable());
            $adapter->query($query);

            //$visibilityInfo = $this->_getVisibilityAttributeInfo();
            $statusInfo     = $this->_getStatusAttributeInfo();

            $select = $this->_getReadAdapter()->select()
                ->from(array('pw' => $this->_productWebsiteTable), array())
                ->joinLeft(
                    array('i' => $this->getMainTable()),
                    'i.category_id = ' . (int)$rootId
                        . ' AND i.store_id = ' . (int) $storeId,
                    array())
                ->join(
                    array('ds' => $statusInfo['table']),
                    "ds.entity_id = pw.post_id AND ds.attribute_id = {$statusInfo['id']} AND ds.store_id = 0",
                    array())
                ->joinLeft(
                    array('ss' => $statusInfo['table']),
                    "ss.entity_id = pw.post_id AND ss.attribute_id = {$statusInfo['id']}"
                        . " AND ss.store_id = " . (int)$storeId,
                    array())
                ->where('i.post_id IS NULL')
                ->where(
                    $this->_getWriteAdapter()->getCheckSql('ss.value_id IS NOT NULL', 'ss.value', 'ds.value') . ' = ?',
                    EM_Blog_Model_Post_Status::STATUS_ENABLED)
                ->columns(array(
                    'category_id'   => new Zend_Db_Expr($rootId),
                    'post_id'    	=> 'pw.post_id',
                    'position'      => new Zend_Db_Expr('0'),
                    'is_parent'     => new Zend_Db_Expr('1')
                ));

            $query = $select->insertFromSelect($this->getMainTable());
            $this->_getWriteAdapter()->query($query);
        }

        return $this;
    }
	
	/**
     * Return array of used root category id - path pairs
     *
     * @return array
     */
    protected function _getRootCategories()
    {
        $rootCategories = array();
        $root = Mage::helper('blog/category')->getRootCategory();
		$rootCategories[$root->getId()] = $root->getPath();
        return $rootCategories;
    }
	
	/**
     * Get array with store|website|root_categry path information
     *
     * @return array
     */
    protected function _getStoresInfo()
    {
        if (is_null($this->_storesInfo)) {
            $adapter = $this->_getReadAdapter();
            $select = $adapter->select()
                ->from(array('s' => $this->getTable('core/store')), array('store_id'))
                ->join(
                    array('c' => $this->getTable('blog/category')),
                    'c.entity_id = sg.root_category_id AND c.entity_id='.self::STORE_ROOT_ID,
                    array(
                        'root_path' => 'path',
                        'root_id'   => 'entity_id'
                    )
                );
            $this->_storesInfo = $adapter->fetchAll($select);
        }

        return $this->_storesInfo;
    }
	
	/**
     * Get status post attribute information
     *
     * @return array array('id' => $id, 'table'=>$table)
     */
    protected function _getStatusAttributeInfo()
    {
        $statusAttribute = Mage::getSingleton('eav/config')->getAttribute(EM_Blog_Model_Post::ENTITY, 'status');
        $info = array(
            'id'    => $statusAttribute->getId() ,
            'table' => $statusAttribute->getBackend()->getTable()
        );
        return $info;
    }
	
	/**
     * Add product association with root store category for products which are not assigned to any another category
     *
     * @param int | array $productIds
     * @return Mage_Catalog_Model_Resource_Category_Indexer_Product
     */
    protected function _refreshRootRelations($postIds)
    {
        //$visibilityInfo = $this->_getVisibilityAttributeInfo();
        $statusInfo     = $this->_getStatusAttributeInfo();
        $adapter = $this->_getWriteAdapter();
        /**
         * Insert anchor categories relations
         */
        $isParent = new Zend_Db_Expr('0');
        $position = new Zend_Db_Expr('0');
        $select = $this->_getReadAdapter()->select()
            ->distinct(true)
            ->from(array('pw'  => $this->_productWebsiteTable), array())
            //->joinInner(array('g'   => $this->_groupTable), 'g.website_id=pw.website_id', array())
            //->joinInner(array('s'   => $this->_storeTable), 's.group_id=g.group_id', array())
            ->joinInner(array('rc'  => $this->_categoryTable), 'rc.entity_id='.self::STORE_ROOT_ID,
                array('entity_id'))
            ->joinLeft(array('cp'   => $this->_categoryProductTable), 'cp.product_id=pw.product_id',
                array('pw.product_id', $position, $isParent, 's.store_id'))
            /*->joinLeft(
                array('dv'=>$visibilityInfo['table']),
                "dv.entity_id=pw.product_id AND dv.attribute_id={$visibilityInfo['id']} AND dv.store_id=0",
                array())
            ->joinLeft(
                array('sv'=>$visibilityInfo['table']),
                "sv.entity_id=pw.product_id AND sv.attribute_id={$visibilityInfo['id']} AND sv.store_id=s.store_id",
                array('visibility' => $adapter->getCheckSql('sv.value_id IS NOT NULL',
                    $adapter->quoteIdentifier('sv.value'),
                    $adapter->quoteIdentifier('dv.value')
                ))
            )*/
            ->joinLeft(
                array('ds'=>$statusInfo['table']),
                "ds.entity_id=pw.product_id AND ds.attribute_id={$statusInfo['id']} AND ds.store_id=0",
                array())
            ->joinLeft(
                array('ss'=>$statusInfo['table']),
                "ss.entity_id=pw.product_id AND ss.attribute_id={$statusInfo['id']} AND ss.store_id=s.store_id",
                array())
            /**
             * Condition for anchor or root category (all products should be assigned to root)
             */
            ->where('cp.product_id IS NULL')
            ->where(
                    $adapter->getCheckSql('ss.value_id IS NOT NULL',
                        $adapter->quoteIdentifier('ss.value'),
                        $adapter->quoteIdentifier('ds.value')
                    ) . ' = ?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->where('pw.product_id IN(?)', $postIds);

        $sql = $select->insertFromSelect($this->getMainTable());
        $this->_getWriteAdapter()->query($sql);

        $select = $this->_getReadAdapter()->select()
            ->from(array('pw' => $this->_productWebsiteTable), array())
            ->joinInner(array('g' => $this->_groupTable), 'g.website_id = pw.website_id', array())
            ->joinInner(array('s' => $this->_storeTable), 's.group_id = g.group_id', array())
            ->joinLeft(
                array('i'  => $this->getMainTable()),
                'i.product_id = pw.product_id AND i.category_id = g.root_category_id', array())
            ->joinLeft(
                array('dv' => $visibilityInfo['table']),
                "dv.entity_id = pw.product_id AND dv.attribute_id = {$visibilityInfo['id']} AND dv.store_id = 0",
                array())
            ->joinLeft(
                array('sv' => $visibilityInfo['table']),
                "sv.entity_id = pw.product_id AND sv.attribute_id = {$visibilityInfo['id']}"
                    . " AND sv.store_id = s.store_id",
                array())
            ->join(
                array('ds' => $statusInfo['table']),
                "ds.entity_id = pw.product_id AND ds.attribute_id = {$statusInfo['id']} AND ds.store_id = 0",
                array())
            ->joinLeft(
                array('ss' => $statusInfo['table']),
                "ss.entity_id = pw.product_id AND ss.attribute_id = {$statusInfo['id']} AND ss.store_id = s.store_id",
                array())
            ->where('i.product_id IS NULL')
            ->where(
                $adapter->getCheckSql('ss.value_id IS NOT NULL', 'ss.value', 'ds.value') . '=?',
                Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->where('pw.product_id IN(?)', $postIds)
            ->columns(array(
                'category_id'   => 'g.root_category_id',
                'product_id'    => 'pw.product_id',
                'position'      => $position,
                'is_parent'     => new Zend_Db_Expr('1'),
                'store_id'      => 's.store_id',
                'visibility'    => $adapter->getCheckSql('sv.value_id IS NOT NULL', 'sv.value', 'dv.value'),
            ));

        $sql = $select->insertFromSelect($this->getMainTable());
        $this->_getWriteAdapter()->query($sql);

        return $this;
    }
}
