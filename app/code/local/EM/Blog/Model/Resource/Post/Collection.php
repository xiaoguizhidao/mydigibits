<?php
class EM_Blog_Model_Resource_Post_Collection extends EM_Blog_Model_Resource_Collection_Abstract
{
	/**
     * Product limitation filters
     * Allowed filters
     *  store_id                int;
     *  category_id             int;
     *  category_is_anchor      int;
     *  store_table             string;
     *
     * @var array
     */
    protected $_productLimitationFilters     = array();
	
	/**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('blog/post');
    }
	
	/**
     * Add store availability filter. Include availability product
     * for store website
     *
     * @param mixed $store
     * @return EM_Blog_Model_Resource_Post_Collection
     */
    public function addStoreFilter($store = null)
    {
        if ($store === null) {
            $store = $this->getStoreId();
        }
        $store = Mage::app()->getStore($store);

        if (!$store->isAdmin()) {
            $this->setStoreId($store);
            $this->_productLimitationFilters['store_id'] = $store->getId();
            $this->_applyProductLimitations();
        }

        return $this;
    }

    /**
     * Add website filter to collection
     *
     * @param unknown_type $websites
     * @return EM_Blog_Model_Resource_Post_Collection
     */
    public function addWebsiteFilter($websites = null)
    {
        if (!is_array($websites)) {
            $websites = array(Mage::app()->getWebsite($websites)->getId());
        }

        $this->_productLimitationFilters['website_ids'] = $websites;
        //$this->_applyProductLimitations();

        return $this;
    }
	
	/**
     * Retreive product count select for categories
     *
     * @return Varien_Db_Select
     */
    public function getProductCountSelect()
    {
        if ($this->_productCountSelect === null) {
            $this->_productCountSelect = clone $this->getSelect();
            $this->_productCountSelect->reset(Zend_Db_Select::COLUMNS)
                ->reset(Zend_Db_Select::GROUP)
                ->reset(Zend_Db_Select::ORDER)
                ->distinct(false)
                ->join(array('count_table' => $this->getTable('blog/category_post')),
                    'count_table.post_id = e.entity_id',
                    array(
                        'count_table.category_id',
                        'post_count' => new Zend_Db_Expr('COUNT(DISTINCT count_table.post_id)')
                    )
                )
                ->where('count_table.store_id = ?', $this->getStoreId())
                ->group('count_table.category_id');
        }

        return $this->_productCountSelect;
    }

    /**
     * Destruct product count select
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function unsProductCountSelect()
    {
        $this->_productCountSelect = null;
        return $this;
    }

    /**
     * Adding product count to categories collection
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $categoryCollection
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addCountToCategories($categoryCollection)
    {
        $isAnchor    = array();
        $isNotAnchor = array();
        foreach ($categoryCollection as $category) {
            if ($category->getIsAnchor()) {
                $isAnchor[]    = $category->getId();
            } else {
                $isNotAnchor[] = $category->getId();
            }
        }
        $productCounts = array();
        if ($isAnchor || $isNotAnchor) {
            $select = $this->getProductCountSelect();

            Mage::dispatchEvent(
                'catalog_product_collection_before_add_count_to_categories',
                array('collection' => $this)
            );

            if ($isAnchor) {
                $anchorStmt = clone $select;
                $anchorStmt->limit(); //reset limits
                $anchorStmt->where('count_table.category_id IN (?)', $isAnchor);
                $productCounts += $this->getConnection()->fetchPairs($anchorStmt);
                $anchorStmt = null;
            }
            if ($isNotAnchor) {
                $notAnchorStmt = clone $select;
                $notAnchorStmt->limit(); //reset limits
                $notAnchorStmt->where('count_table.category_id IN (?)', $isNotAnchor);
                $notAnchorStmt->where('count_table.is_parent = 1');
                $productCounts += $this->getConnection()->fetchPairs($notAnchorStmt);
                $notAnchorStmt = null;
            }
            $select = null;
            $this->unsProductCountSelect();
        }

        foreach ($categoryCollection as $category) {
            $_count = 0;
            if (isset($productCounts[$category->getId()])) {
                $_count = $productCounts[$category->getId()];
            }
            $category->setProductCount($_count);
        }

        return $this;
    }
	
	/**
     * Prepare limitation filters
     *
     * @return EM_Blog_Model_Resource_Post_Collection
     */
    protected function _prepareProductLimitationFilters()
    {
        if (!isset($this->_productLimitationFilters['store_id'])
        ) {
            $this->_productLimitationFilters['store_id'] = $this->getStoreId();
        }
        if (isset($this->_productLimitationFilters['category_id'])
            && !isset($this->_productLimitationFilters['store_id'])
        ) {
            $this->_productLimitationFilters['store_id'] = $this->getStoreId();
        }
        if (isset($this->_productLimitationFilters['store_id'])
            && !isset($this->_productLimitationFilters['category_id'])
        ) {
            $this->_productLimitationFilters['category_id'] = Mage::helper('blog/category')->getRootCategory()->getId();
        }

        return $this;
    }
	
	/**
     * Specify category filter for post collection
     *
     * @param EM_Blog_Model_Category $category
     * @return EM_Blog_Model_Resource_Post_Collection
     */
    public function addCategoryFilter(EM_Blog_Model_Category $category)
    {
        $this->_productLimitationFilters['category_id'] = $category->getId();
        if ($category->getIsAnchor()) {
            unset($this->_productLimitationFilters['category_is_anchor']);
        } else {
            $this->_productLimitationFilters['category_is_anchor'] = 1;
        }

        if ($this->getStoreId() == EM_Blog_Model_Abstract::DEFAULT_STORE_ID) {
            $this->_applyZeroStoreProductLimitations();
        } else {
            $this->_applyProductLimitations();
        }

        return $this;
    }
	
	/**
     * Apply limitation filters to collection base on API
     * Method allows using one time category product table
     * for combinations of category_id filter states
     *
     * @return EM_Blog_Model_Resource_Post_Collection
     */
    protected function _applyZeroStoreProductLimitations()
    {
        $filters = $this->_productLimitationFilters;

        $conditions = array(
            'cat_pro.post_id=e.entity_id',
            $this->getConnection()->quoteInto('cat_pro.category_id=?', $filters['category_id'])
        );
        $joinCond = join(' AND ', $conditions);

        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (isset($fromPart['cat_pro'])) {
            $fromPart['cat_pro']['joinCondition'] = $joinCond;
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        }
        else {
            $this->getSelect()->join(
                array('cat_pro' => $this->getTable('blog/category_post')),
                $joinCond,
                array('cat_index_position' => 'position')
            );
        }
        $this->_joinFields['position'] = array(
            'table' => 'cat_pro',
            'field' => 'position',
        );

        return $this;
    }
	
	/**
     * Apply limitation filters to collection
     * Method allows using one time category product index table (or product website table)
     * for different combinations of store_id/category_id/visibility filter states
     * Method supports multiple changes in one collection object for this parameters
     *
     * @return EM_Blog_Model_Resource_Post_Collection
     */
    protected function _applyProductLimitations()
    {
        $this->_prepareProductLimitationFilters();
        $filters = $this->_productLimitationFilters;

        if (!isset($filters['category_id'])) {
            return $this;
        }
		
		$conditions = array(
            'cat_index.post_id=e.entity_id'
        );
        
        $conditions[] = $this->getConnection()
            ->quoteInto('cat_index.category_id=?', $filters['category_id']);
        if (isset($filters['category_is_anchor'])) {
            $conditions[] = $this->getConnection()
                ->quoteInto('cat_index.is_parent=?', $filters['category_is_anchor']);
        }

        $joinCond = join(' AND ', $conditions);
        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (isset($fromPart['cat_index'])) {
            $fromPart['cat_index']['joinCondition'] = $joinCond;
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        }
        else {
            $this->getSelect()->join(
                array('cat_index' => $this->getTable('blog/category_post_index')),
                $joinCond,
                array('cat_index_position' => 'position')
            );
        }

        //$this->_productLimitationJoinStore();

        Mage::dispatchEvent('catalog_product_collection_apply_limitations_after', array(
            'collection'    => $this
        ));

        return $this;
    }
	
	
}
?>