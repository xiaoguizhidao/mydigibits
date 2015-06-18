<?php
class EM_Blog_Model_Post extends EM_Blog_Model_Abstract
{
	/**
	* Maps to the array key from Setup.php::getDefaultEntities()
	*/
    const ENTITY = 'blog_post';
	/**
     * Model post prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'blog';

    /**
     * Name of the post object
     *
     * @var string
     */
    protected $_eventObject = 'post';
	/**
     * Initialize post model
     */
    function _construct()
    {
        $this->_init('blog/post');
    }
	
	/**
     * Retrieve Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->getData('store_id');
        }
        return Mage::app()->getStore()->getId();
    }
	
	/**
     * Set assigned category IDs array to product
     *
     * @param array|string $ids
     * @return EM_Blog_Model_Post
     */
    public function setCategoryIds($ids)
    {
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        } elseif (!is_array($ids)) {
            Mage::throwException(Mage::helper('blog')->__('Invalid category IDs.'));
        }
        foreach ($ids as $i => $v) {
            if (empty($v)) {
                unset($ids[$i]);
            }
        }

        $this->setData('category_ids', $ids);
        return $this;
    }
	
	/**
     * Retrieve assigned category Ids
     *
     * @return array
     */
    public function getCategoryIds()
    {
        if (! $this->hasData('category_ids')) {
            $wasLocked = false;
            if ($this->isLockedAttribute('category_ids')) {
                $wasLocked = true;
                $this->unlockAttribute('category_ids');
            }
            $ids = $this->_getResource()->getCategoryIds($this);
            $this->setData('category_ids', $ids);
            if ($wasLocked) {
                $this->lockAttribute('category_ids');
            }
        }

        return (array) $this->_getData('category_ids');
    }
	
	
	
	/**
     * Retrieve link instance
     *
     * @return  EM_Blog_Model_Post_Link
     */
    public function getLinkInstance()
    {
        if (!$this->_linkInstance) {
            $this->_linkInstance = Mage::getSingleton('blog/post_link');
        }
        return $this->_linkInstance;
    }
	
	/**
     * Saving product type related data and init index
     *
     * @return EM_Blog_Model_Post
     */
    protected function _afterSave()
    {
        $this->getLinkInstance()->saveProductRelations($this);

        $result = parent::_afterSave();
		Mage::getSingleton('index/indexer')->processEntityAction(
            $this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
        );
        return $result;
    }

	/**
     * Retrieve array of related roducts
     *
     * @return array
     */
    public function getRelatedProducts()
    {
        if (!$this->hasRelatedProducts()) {
            $products = array();
            $collection = $this->getRelatedProductCollection();
            foreach ($collection as $product) {
                $products[] = $product;
            }
            $this->setRelatedProducts($products);
        }
        return $this->getData('related_products');
    }

	/**
     * Retrieve collection related product
     *
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function getRelatedProductCollection()
    {
        $collection = $this->getLinkInstance()->useRelatedLinks()
            ->getProductCollection()
            ->setIsStrongMode();
        $collection->setProduct($this);
        return $collection;
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

	/*
		Get tags of post
		@return EM_Blog_Model_Resource_Tag_Collection
	*/
	public function getTags()
    {
		return Mage::getResourceSingleton('blog/tag')->getTagsByPost($this);
    }

	/*
		Get author of post
		@return Mage_Admin_Model_User
	*/
	public function getAuthor(){
		return Mage::getModel('admin/user')->load($this->getAuthorId());
	}
	
}
?>