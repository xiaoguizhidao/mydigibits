<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Bloc post link model
 *
 * @method Mage_Catalog_Model_Resource_Product_Link _getResource()
 * @method Mage_Catalog_Model_Resource_Product_Link getResource()
 * @method int getProductId()
 * @method Mage_Catalog_Model_Product_Link setProductId(int $value)
 * @method int getLinkedProductId()
 * @method Mage_Catalog_Model_Product_Link setLinkedProductId(int $value)
 * @method int getLinkTypeId()
 * @method Mage_Catalog_Model_Product_Link setLinkTypeId(int $value)
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class EM_Blog_Model_Post_Link extends Mage_Core_Model_Abstract
{
    const LINK_TYPE_RELATED     = 1;
    
    protected $_attributeCollection = null;

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('blog/post_link');
    }

    public function useRelatedLinks()
    {
        $this->setLinkTypeId(self::LINK_TYPE_RELATED);
        return $this;
    }

    /**
     * Retrieve table name for attribute type
     *
     * @param   string $type
     * @return  string
     */
    public function getAttributeTypeTable($type)
    {
        return $this->_getResource()->getAttributeTypeTable($type);
    }

    /**
     * Retrieve linked post collection
     */
    public function getProductCollection()
    {
        $collection = Mage::getResourceModel('blog/post_link_post_collection')
            ->setLinkModel($this);
        return $collection;
    }

    /**
     * Retrieve link collection
     */
    public function getLinkCollection()
    {
        $collection = Mage::getResourceModel('blog/post_link_collection')
            ->setLinkModel($this);
        return $collection;
    }

    public function getAttributes($type=null)
    {
        if (is_null($type)) {
            $type = $this->getLinkTypeId();
        }
        return $this->_getResource()->getAttributesByType($type);
    }

    /**
     * Save data for product relations
     *
     * @param   EM_Blog_Model_Post $post
     * @return  EM_Blog_Model_Post_Link
     */
    public function saveProductRelations($post)
    {
        $data = $post->getRelatedLinkData();
        if (!is_null($data)) {
            $this->_getResource()->saveProductLinks($post, $data, self::LINK_TYPE_RELATED);
        }
        return $this;
    }
}
