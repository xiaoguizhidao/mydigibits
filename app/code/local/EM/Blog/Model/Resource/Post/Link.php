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
 * Blog post link resource model
 *
 * @category    EM
 * @package     EM_Blog
 * @author      Emthemes <emthemes.com>
 */
class EM_Blog_Model_Resource_Post_Link extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Product Link Attributes Table
     *
     * @var string
     */
    protected $_attributesTable;

    /**
     * Define main table name and attributes table
     */
    protected function _construct()
    {
        $this->_init('blog/post_link', 'link_id');
        $this->_attributesTable = $this->getTable('blog/post_link_attribute');
    }

    /**
     * Save Product Links process
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     * @param int $typeId
     * @return Mage_Catalog_Model_Resource_Product_Link
     */
    public function saveProductLinks($product, $data, $typeId)
    {
        if (!is_array($data)) {
            $data = array();
        }

        $attributes = $this->getAttributesByType($typeId);
        $adapter    = $this->_getWriteAdapter();

        $bind   = array(
            ':post_id'    => (int)$product->getId(),
            ':link_type_id'  => (int)$typeId
        );
        $select = $adapter->select()
            ->from($this->getMainTable(), array('linked_post_id', 'link_id'))
            ->where('post_id = :post_id')
            ->where('link_type_id = :link_type_id');

        $links   = $adapter->fetchPairs($select, $bind);

        $deleteIds = array();
        foreach($links as $linkedProductId => $linkId) {
            if (!isset($data[$linkedProductId])) {
                $deleteIds[] = (int)$linkId;
            }
        }
        if (!empty($deleteIds)) {
            $adapter->delete($this->getMainTable(), array(
                'link_id IN (?)' => $deleteIds,
            ));
        }

        foreach ($data as $linkedProductId => $linkInfo) {
            $linkId = null;
            if (isset($links[$linkedProductId])) {
                $linkId = $links[$linkedProductId];
                unset($links[$linkedProductId]);
            } else {
                $bind = array(
                    'post_id'        => $product->getId(),
                    'linked_post_id' => $linkedProductId,
                    'link_type_id'      => $typeId
                );
                $adapter->insert($this->getMainTable(), $bind);
                $linkId = $adapter->lastInsertId($this->getMainTable());
            }

            foreach ($attributes as $attributeInfo) {
                $attributeTable = $this->getAttributeTypeTable($attributeInfo['type']);
                if ($attributeTable) {
                    if (isset($linkInfo[$attributeInfo['code']])) {
                        $value = $this->_prepareAttributeValue($attributeInfo['type'],
                            $linkInfo[$attributeInfo['code']]);
                        $bind = array(
                            'post_link_attribute_id' => $attributeInfo['id'],
                            'link_id'                   => $linkId,
                            'value'                     => $value
                        );
                        $adapter->insertOnDuplicate($attributeTable, $bind, array('value'));
                    } else {
                        $adapter->delete($attributeTable, array(
                            'link_id = ?'                   => $linkId,
                            'post_link_attribute_id = ?' => $attributeInfo['id']
                        ));
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Prepare link attribute value by attribute type
     *
     * @param string $type
     * @param mixed $value
     * @return mixed
     */
    protected function _prepareAttributeValue($type, $value)
    {
        if ($type == 'int') {
            $value = (int)$value;
        } elseif ($type == 'decimal') {
            $value = (float)sprintf('%F', $value);
        }
        return $value;
    }

    /**
     * Retrieve product link attributes by link type
     *
     * @param int $typeId
     * @return array
     */
    public function getAttributesByType($typeId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_attributesTable, array(
                'id'    => 'post_link_attribute_id',
                'code'  => 'post_link_attribute_code',
                'type'  => 'data_type'
            ))
            ->where('link_type_id = ?', $typeId);
        return $adapter->fetchAll($select);
    }

    /**
     * Returns table for link attribute by attribute type
     *
     * @param string $type
     * @return string
     */
    public function getAttributeTypeTable($type)
    {
        return $this->getTable('blog/post_link_attribute_' . $type);
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     * @param int $typeId
     * @return array
     */
    public function getChildrenIds($parentId, $typeId)
    {
        $adapter     = $this->_getReadAdapter();
        $childrenIds = array();
        $bind        = array(
            ':post_id'    => (int)$parentId,
            ':link_type_id'  => (int)$typeId
        );
        $select = $adapter->select()
            ->from(array('l' => $this->getMainTable()), array('linked_post_id'))
            ->where('post_id = :post_id')
            ->where('link_type_id = :link_type_id');
        if ($typeId == Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED) {
            $select->join(
                array('e' => $this->getTable('blog/post')),
                'e.entity_id = l.linked_post_id AND e.required_options = 0',
                array()
            );
        }

        $childrenIds[$typeId] = array();
        $result = $adapter->fetchAll($select, $bind);
        foreach ($result as $row) {
            $childrenIds[$typeId][$row['linked_post_id']] = $row['linked_post_id'];
        }

        return $childrenIds;
    }

    /**
     * Retrieve parent ids array by required child
     *
     * @param int|array $childId
     * @param int $typeId
     * @return array
     */
    public function getParentIdsByChild($childId, $typeId)
    {
        $parentIds  = array();
        $adapter    = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('post_id', 'linked_post_id'))
            ->where('linked_post_id IN(?)', $childId)
            ->where('link_type_id = ?', $typeId);

        $result = $adapter->fetchAll($select);
        foreach ($result as $row) {
            $parentIds[] = $row['post_id'];
        }

        return $parentIds;
    }
}
