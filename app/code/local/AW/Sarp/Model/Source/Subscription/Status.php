<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp
 * @version    1.9.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Sarp_Model_Source_Subscription_Status extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrive all attribute options
     *
     * @return array
     */

    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    public function toOptionArray()
    {
        return array(
            array('value' => AW_Sarp_Model_Subscription::STATUS_ENABLED, 'label' => Mage::helper('sarp')->__('Active')),
            array('value' => AW_Sarp_Model_Subscription::STATUS_SUSPENDED, 'label' => Mage::helper('sarp')->__('Suspended')),
            array('value' => AW_Sarp_Model_Subscription::STATUS_SUSPENDED_BY_CUSTOMER, 'label' => Mage::helper('sarp')->__('Suspended by customer')),
            array('value' => AW_Sarp_Model_Subscription::STATUS_EXPIRED, 'label' => Mage::helper('sarp')->__('Expired')),
            array('value' => AW_Sarp_Model_Subscription::STATUS_CANCELED, 'label' => Mage::helper('sarp')->__('Canceled'))
        );
    }

    /**
     * Returns label for value
     * @param string $value
     * @return string
     */
    public function getLabel($value)
    {
        $options = $this->toOptionArray();
        foreach ($options as $v) {
            if ($v['value'] == $value) {
                return $v['label'];
            }
        }
        return '';
    }

    /**
     * Returns array ready for use by grid
     * @return array
     */
    public function getGridOptions()
    {
        $items = $this->getAllOptions();
        $out = array();
        foreach ($items as $item) {
            $out[$item['value']] = $item['label'];
        }
        return $out;
    }

}
