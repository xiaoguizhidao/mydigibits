<?php

class Ebizmarts_SagePaySuite_Model_Total_Charge extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    public function __construct() {
        $this->setCode('surcharge');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address) {

        parent::collect($address);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }

        $baseSurcharge = Mage::helper('sagepaysuite/surcharge')->getChargeAmount($address);
        $surcharge = Mage::app()->getStore()->convertPrice($baseSurcharge);

        if($surcharge) {
            $this->_addBaseAmount($baseSurcharge);
            $this->_addAmount($surcharge);
        }

        return $this;

    }

    /**
     * Add shipping totals information to address object
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Shipping
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address) {

        $amount = $address->getSurchargeAmount();
        //$baseSurcharge = Mage::helper('sagepaysuite/surcharge')->getChargeAmount($address);
        //$amount = Mage::app()->getStore()->convertPrice($baseSurcharge);

        if ($amount != 0) {

            $title = Mage::helper('sagepaysuite')->__('Credit Card Surcharge');

            $address->addTotal(array(
                    'code' => $this->getCode(),
                    'title' => $title,
                    'value' => $amount
            ));
        }
        return $this;

    }

    /**
     * Get Shipping label
     *
     * @return string
     */
    public function getLabel() {
        return Mage::helper('sagepaysuite')->__('Credit Card Surcharge');
    }

}