<?php
/**
 * InstantSearchPlus (Autosuggest)

 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Mage
 * @package    InstantSearchPlus
 * @copyright  Copyright (c) 2014 Fast Simon (http://www.instantsearchplus.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Autocompleteplus_Autosuggest_Model_Observer extends Mage_Core_Model_Abstract
{

    private $imageField;
    private $standardImageFields=array();
    private $currency;

    public function adminhtml_controller_catalogrule_prepare_save($observer){

        //Mage::log(print_r($observer,true),null,'autocompleteplus.log');
    }

    public function catalogrule_after_apply($observer){

        //Mage::log('apply:    '.print_r($observer,true),null,'autocompleteplus.log');
    }


    public function catalog_controller_product_init($observer){


        try{
            $helper=Mage::helper('autocompleteplus_autosuggest');

            $_tableprefix = (string)Mage::getConfig()->getTablePrefix();

            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            $tblExist=$write->showTableStatus($_tableprefix.'autocompleteplus_config');

            if(!$tblExist){return;}

            $keyList=$write->describeTable($_tableprefix.'autocompleteplus_config');

            if(!isset($keyList['site_url'])){return;}

            $sqlFetch    ='SELECT * FROM '. $_tableprefix.'autocompleteplus_config WHERE id = 1';

            $config=$write->fetchAll($sqlFetch);

            if(isset($config[0]['site_url'])){

                $old_url=$config[0]['site_url'];

            }else{
                $old_url='no_old_url';
            }

            if(isset($config[0]['licensekey'])){

                $licensekey=$config[0]['licensekey'];

            }else{
                $licensekey='no_uuid';
            }


            //getting site url
            $url=$helper->getConfigDataByFullPath('web/unsecure/base_url');

            if($old_url!=$url){

                $command="http://magento.autocompleteplus.com/ext_update_host";
                $data=array();
                $data['old_url']=$old_url;
                $data['new_url']=$url;
                $data['uuid']=$licensekey;

                $res=$helper->sendPostCurl($command,$data);

                $result=json_decode($res);

                if(strtolower($result->status)=='ok'){
                    $sql='UPDATE '. $_tableprefix.'autocompleteplus_config  SET site_url=? WHERE id = 1';

                    $write->query($sql, array($url));
                }

                Mage::log(print_r($res,true),null,'autocompleteplus.log');
            }

        }catch(Exception $e){
            Mage::log($e->getMessage(),null,'autocompleteplus.log');
        }


    }


    public function catalog_product_save_after_depr($observer){
        $product=$observer->getProduct();
        $this->imageField=Mage::getStoreConfig('autocompleteplus/config/imagefield');
        if(!$this->imageField){
            $this->imageField='thumbnail';
        }

        $this->standardImageFields=array('image','small_image','thumbnail');
        $this->currency=Mage::app()->getStore()->getCurrentCurrencyCode();

        $domain =Mage::getStoreConfig('web/unsecure/base_url');
        $key    =$this->getKey();

        $mage=Mage::getVersion();
        $ext=(string) Mage::getConfig()->getNode()->modules->Autocompleteplus_Autosuggest->version;

        $xml='<?xml version="1.0"?>';

        $xml.='<catalog version="'.$ext.'" magento="'.$mage.'">';

        $xml.=$this->__getProductData($product);

        $xml.='</catalog>';

        $data=array(
            'site'=>$domain,
            'key'=>$key,
            'catalog'=>$xml
        );

        $res=$this->__sendUpdate($data);

        Mage::log(print_r($res,true),null,'autocomplete.log');
    }

    public function catalog_product_save_after($observer){
        date_default_timezone_set('Asia/Jerusalem');

        $product=$observer->getProduct();

        $origData=$observer->getProduct()->getOrigData();

        $storeId=$product->getStoreId();

        $productId=$product->getId();

        $sku=$product->getSku();
        
//         if ($sku == null || $productId == null){
//             Mage::log('catalog_product_save_after - either sku null or identifier is null', null, 'autocompleteplus.log');
//             return;
//         }

        if(is_array($origData)){
            if(array_key_exists('sku',$origData)){

                $oldSku=$origData['sku'];

                if($sku!=$oldSku){

                    $this->__writeproductDeletion($oldSku,$productId,$storeId, $product);
                }

            }
        }

        $dt     = strtotime('now');
        //$mysqldate = date( 'Y-m-d h:m:s', $dt );
        
        try{
            $_tableprefix = (string)Mage::getConfig()->getTablePrefix();
            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $tblExist = $write->showTableStatus($_tableprefix.'autocompleteplus_batches');

            if(!$tblExist){return;}            
            try{
                if ($storeId == 0 && method_exists($product, 'getStoreIds')){
                    $product_stores = $product->getStoreIds();
                } else {
                    $product_stores = array($storeId);
                }
            } catch (Exception $e){
                $product_stores = array($storeId);
            } 
            
            foreach ($product_stores as $product_store){
                $sqlFetch = 'SELECT * FROM '. $_tableprefix.'autocompleteplus_batches WHERE sku = ? AND store_id=?';
                $updates = $write->fetchAll($sqlFetch, array($sku, $product_store));
                
                if($updates&&count($updates) != 0){
                    $sql = 'UPDATE '. $_tableprefix.'autocompleteplus_batches  SET update_date=?,action=? WHERE sku = ? AND store_id=?';
                    $write->query($sql, array($dt, "update", $sku, $product_store));
                }else{
                    $sql='INSERT INTO '. $_tableprefix.'autocompleteplus_batches  (product_id,store_id,update_date,action,sku) VALUES (?,?,?,?,?)';
                    $write->query($sql, array($productId, $product_store, $dt, "update", $sku));
                }
                try{
                    $helper = Mage::helper('autocompleteplus_autosuggest');
                    if ($helper->isChecksumTableExists()){
                        $checksum = $helper->calculateChecksum($product);
                        $helper->updateSavedProductChecksum($_tableprefix, $read, $write, $productId, $sku, $product_store, $checksum);
                    }
                } catch (Exception $e){
                    Mage::log('checksum failed - ' . $e->getMessage(), null, 'autocompleteplus.log');
                }
            }

        }catch(Exception $e){
            Mage::log($e->getMessage(),null,'autocompleteplus.log');
        }
    }

    private function __getProductData($product){

        $sku      =$product->getSku();

        $status=$product->isInStock();
        $stockItem = $product->getStockItem();
        $storeId=$product->getStoreId();


        if($stockItem&&$stockItem->getIsInStock()&&$status)
        {
            $sell=1;
        }else{
            $sell=0;
        }

        $price       =$this->getPrice($product);

        $productUrl       =Mage::helper('catalog/product')->getProductUrl($product->getId());
        $prodDesc         =$product->getDescription();
        $prodShortDesc    =$product->getShortDescription();
        $prodName         =$product->getName();

        try{

            if(in_array($this->imageField,$this->standardImageFields)){
                $prodImage   =Mage::helper('catalog/image')->init($product, $this->imageField);
            }else{
                $function='get'.$this->imageField;
                $prodImage  =$product->$function();
            }

        }catch(Exception $e){
            $prodImage='';
        }


        $visibility       =$product->getVisibility();


        $row='<product store="'.$storeId.'" currency="'.$this->currency.'" visibility="'.$visibility.'" price="'.$price.'" url="'.$productUrl.'"  thumbs="'.$prodImage.'" selleable="'.$sell.'" action="update" >';
        $row.='<description><![CDATA['.$prodDesc.']]></description>';
        $row.='<short><![CDATA['.$prodShortDesc.']]></short>';
        $row.='<name><![CDATA['.$prodName.']]></name>';
        $row.='<sku><![CDATA['.$sku.']]></sku>';
        $row.='</product>';

        return $row;
    }



    private function __makeSafeString($str){
        $str=strip_tags($str);
        $str=str_replace('"','',$str);
        $str=str_replace("'",'',$str);
        $str=str_replace('/','',$str);
        $str=str_replace('<','',$str);
        $str=str_replace('>','',$str);
        $str=str_replace('\\','',$str);
        return $str;
    }

    private function __sendUpdate($data){

        $ch=curl_init();
        $command='http://magento.autocompleteplus.com/update';
        curl_setopt($ch, CURLOPT_URL, $command);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        //curl_setopt($ch,CURLOPT_POST,0);
        if(!empty($data)){
            curl_setopt_array($ch, array(
                CURLOPT_POSTFIELDS => $data,
            ));
        }

        return curl_exec($ch);
    }


    public function getKey(){

        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $_tableprefix = (string)Mage::getConfig()->getTablePrefix();

        $tblExist=$write->showTableStatus($_tableprefix.'autocompleteplus_config');

        if(!$tblExist){return;}

        $sql='SELECT * FROM `'.$_tableprefix.'autocompleteplus_config` WHERE `id` =1';

        $licenseData=$read->fetchAll($sql);

        $key=$licenseData[0]['licensekey'];

        return $key;
    }

    private function getPrice($product){
        $price = 0;
        $helper=Mage::helper('autocompleteplus_autosuggest');
        if ($product->getTypeId()=='grouped'){

            $helper->prepareGroupedProductPrice($product);
            $_minimalPriceValue = $product->getPrice();

            if($_minimalPriceValue){
                $price=$_minimalPriceValue;
            }

        }elseif($product->getTypeId()=='bundle'){

            if(!$product->getFinalPrice()){
                $price=$helper->getBundlePrice($product);
            }else{
                $price=$product->getFinalPrice();
            }

        }else{
            $price       =$product->getFinalPrice();
        }

        if(!$price){
            $price=0;
        }
        return $price;
    }

    public function catalog_product_delete_before($observer){

        $product=$observer->getProduct();

        $storeId=$product->getStoreId();

        $productId=$product->getId();

        $sku=$product->getSku();

        $this->__writeproductDeletion($sku,$productId,$storeId, $product);

    }

    private function __writeproductDeletion($sku, $productId, $storeId, $product = null){
        $dt     = strtotime('now');
        //$mysqldate = date( 'Y-m-d h:m:s', $dt );

        try{
//             if ($productId == null){
//                 Mage::log('__writeproductDeletion - identifier is null', null, 'autocompleteplus.log');
//                 return;
//             }
            
            $_tableprefix = (string)Mage::getConfig()->getTablePrefix();

            $read = Mage::getSingleton('core/resource')->getConnection('core_read');

            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            $tblExist=$write->showTableStatus($_tableprefix.'autocompleteplus_batches');

            if(!$tblExist){return;}
            
            try{
                $helper = Mage::helper('autocompleteplus_autosuggest');
                if ($helper->isChecksumTableExists()){
                    try{
                        if (!$product){
                            $product = Mage::getModel('catalog/product')->load($productId);
                        }
                        if ($storeId == 0 && method_exists($product, 'getStoreIds')){
                            $product_stores = $product->getStoreIds();
                        } else {
                            $product_stores = array($storeId);
                        }
                    } catch (Exception $e){
                        Mage::log('exception raised: ' .  $e->getMessage(),null,'autocompleteplus.log');
                        $product_stores = array($storeId);
                    }
                    if ($sku == null){
                        $sku = 'dummy_sku';
                    }
                        
                    foreach ($product_stores as $product_store){
                        $sqlFetch = 'SELECT * FROM '. $_tableprefix.'autocompleteplus_batches WHERE product_id = ? AND store_id=?';
                        $updates = $read->fetchAll($sqlFetch, array($productId, $product_store));
                        
                        if($updates && count($updates) != 0){
                            $sql = 'UPDATE '. $_tableprefix.'autocompleteplus_batches SET update_date=?,action=? WHERE product_id = ? AND store_id = ?';
                            $write->query($sql, array($dt, "remove", $productId, $product_store));
                        } else {
                            $sql='INSERT INTO '. $_tableprefix.'autocompleteplus_batches  (product_id,store_id,update_date,action,sku) VALUES (?,?,?,?,?)';
                            $write->query($sql, array($productId, $product_store, $dt, "remove", $sku));
                        }
                        
                        $helper->updateDeletedProductChecksum($_tableprefix, $read, $write, $productId, $sku, $product_store);
                    }
                }
            } catch (Exception $e){
                Mage::log('__writeproductDeletion failed on remove - ' . $e->getMessage(), null, 'autocompleteplus.log');
            }

        }catch(Exception $e){
            Mage::log('__writeproductDeletion: ' . $e->getMessage(),null,'autocompleteplus.log');
        }
    }
    
    public function adminSessionUserLoginSuccess()
    {
        $notifications = array();
        /** @var Autocompleteplus_Autosuggest_Helper_Data $helper */
        $helper  = Mage::helper('autocompleteplus_autosuggest');
        $command = "http://magento.autocompleteplus.com/ext_info?u=". $helper->getKey();
        $res = $helper->sendCurl($command);
        $result = json_decode($res);
        if (isset($result->alerts)) {
            foreach ($result->alerts as $alert) {
                $notification = array(
                    'type'      => (string) $alert->type,
                    'message'   => (string) $alert->message,
                    'timestamp' => (string) $alert->timestamp,
                );
                if (isset($alert->subject)) {
                    $notification['subject'] = (string) $alert->subject;
                }
                $notifications[] = $notification;
            }
        }
        if (!empty($notifications)) {
            Mage::getResourceModel('autocompleteplus_autosuggest/notifications')->addNotifications($notifications);
        }
        $this->sendNotificationMails();
    }

    public function sendNotificationMails()
    {
        /** @var Autocompleteplus_Autosuggest_Model_Mysql4_Notifications_Collection $notifications */
        $notifications = Mage::getModel('autocompleteplus_autosuggest/notifications')->getCollection();
        $notifications->addTypeFilter('email')->addActiveFilter();
        foreach ($notifications as $notification) {
            $this->_sendStatusMail($notification);
        }
    }

    /**
     * @param Autocompleteplus_Autosuggest_Model_Notifications $notification
     */
    protected function _sendStatusMail($notification)
    {

        /** @var Autocompleteplus_Autosuggest_Helper_Data $helper */
        $helper  = Mage::helper('autocompleteplus_autosuggest');
        // Getting site owner email
        $storeMail = $helper->getConfigDataByFullPath('autocompleteplus/config/store_email');
        if ($storeMail) {
            $emailTemplate  = Mage::getModel('core/email_template');

            $emailTemplate->loadDefault('autosuggest_status_notification');
            $emailTemplate->setTemplateSubject($notification->getSubject());

            // Get General email address (Admin->Configuration->General->Store Email Addresses)
            $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/email'));
            $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/name'));

            $emailTemplateVariables['message'] = $notification->getMessage();
            $emailTemplate->send($storeMail, null, $emailTemplateVariables);
            $notification->setIsActive(0)
                ->save();
        }
    }
  
}