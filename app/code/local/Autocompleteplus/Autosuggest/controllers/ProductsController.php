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
class Autocompleteplus_Autosuggest_ProductsController extends Mage_Core_Controller_Front_Action
{

    private $imageField='';

    private $standardImageFields=array();
    
    const MAX_NUM_OF_PRODUCTS_CHECKSUM_ITERATION = 250;

    public function sendAction(){

        set_time_limit (1800);

        $post = $this->getRequest()->getParams();

        $enabled= Mage::getStoreConfig('autocompleteplus/config/enabled');
        if($enabled=='0'){
            die('The user has disabled autocompleteplus.');
        }

        $imageField=Mage::getStoreConfig('autocompleteplus/config/imagefield');
        if(!$imageField){
            $imageField='thumbnail';
        }

        $useAttributes= Mage::getStoreConfig('autocompleteplus/config/attributes');

        $currency=Mage::app()->getStore()->getCurrentCurrencyCode();

        $standardImageFields=array('image','small_image','thumbnail');

        $startInd     = $post['offset'];
        if(!$startInd){
            $startInd=0;
        }

        $count        = $post['count'];

        //maxim products on one page is 200
        if(!$count||$count>10000){
            $count=10000;
        }
        //retrieving page number
        $pageNum=floor(($startInd/$count));

        //retrieving products collection to check if the offset is not bigger that the product count
        $collection=Mage::getModel('catalog/product')->getCollection();
        
        if(isset($post['store'])&&$post['store']!=''){
            $collection->addStoreFilter($post['store']);
        }


        /* since the retreiving of product count will load the entire collection of products,
         *  we need to annul it in order to get the specified page only
         */
        unset($collection);

        $mage=Mage::getVersion();
        $ext=(string) Mage::getConfig()->getNode()->modules->Autocompleteplus_Autosuggest->version;

        $xml='<?xml version="1.0"?>';
        $xml.='<catalog version="'.$ext.'" magento="'.$mage.'">';


        $productScheme = Mage::getModel('catalog/product');

        if($useAttributes!='0'){
            $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($productScheme->getResource()->getTypeId())
                ->addFieldToFilter('is_user_defined', '1') // This can be changed to any attribute code
                ->load(false);
        }

        $collection=Mage::getModel('catalog/product')->getCollection();
        if(isset($post['store'])&&$post['store']!=''){
            $collection->addStoreFilter($post['store']);
        }


        //setting page+products on the page
        $collection->getSelect()->limit($count,$startInd);//->limitPage($pageNum, $count);//setPage($pageNum, $count)->load();

        $collection->load();
        
        // number of orderes per product section
        if (isset($post['orders']) && $post['orders'] == '1'){
            $product_id_list = array();
            foreach ($collection as $product){
                $product_id_list[] = $product->getId();
            }
            if(isset($post['store'])&&$post['store']!=''){
                $store_id = $post['store'];
            } else {
                $store_id = 1;
            }
            if(isset($post['month_interval'])&&$post['month_interval']!=''){
                $month_interval = $post['month_interval'];
            } else {
                $month_interval = 12;
            }
            $orders_per_product = $this->getOrdersPerProduct($store_id, $product_id_list, $month_interval);
        } else {// end - number of orderes per product section
            $orders_per_product = null;
        }
        
        if(isset($post['checksum']) && $post['checksum'] != ''){
            $is_checksum = $post['checksum'];
            $helper = Mage::helper('autocompleteplus_autosuggest');
            $_tableprefix = (string)Mage::getConfig()->getTablePrefix();
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        } else {
            $is_checksum = 0;
            $helper = null;
            $_tableprefix = null;
            $write = null;
            $read = null;
        }
        
        foreach ($collection as $product) {

            $productCollData=$product->getData();
            $productModel=Mage::getModel('catalog/product')->load($productCollData['entity_id']);

            $categoriesNames='';

            $categories = $productModel->getCategoryCollection()
                ->addAttributeToSelect('name');

            foreach($categories as $category) {
                $categoriesNames.=$category->getName().':'.$category->getId().';';
            }

            $price       =$this->getPrice($productModel);
            $sku         =$productModel->getSku();

            $stock_status   =$productModel->isInStock();
            $stockItem      = $productModel->getStockItem();

            if($stockItem->getIsInStock()&&$stock_status)
            {
                $sell=1;
            }else{
                $sell=0;
            }

            $productUrl       =Mage::helper('catalog/product')->getProductUrl($productModel->getId());
            
			$prodId           =$productModel->getId();
			$prodDesc         =$productModel->getDescription();
            $prodShortDesc    =$productModel->getShortDescription();
            $prodName         =$productModel->getName();
            
            $visibility       =$productModel->getVisibility();
            
            if(defined('Mage_Catalog_Model_Product_Status::STATUS_ENABLED')){
                if ($productModel->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED){
                    $product_status = 1;
                } else {
                    $product_status = 0;
                }
            } else {
                if ($productModel->getStatus() == 1){
                    $product_status = 1;
                } else {
                    $product_status = 0;
                }
            }

            try{

                if(in_array($imageField,$standardImageFields)){
                    $prodImage   =Mage::helper('catalog/image')->init($productModel, $imageField);
                }else{
                    $function='get'.$imageField;
                    $prodImage  =$productModel->$function();
                }

            }catch(Exception $e){
                $prodImage='';
            }

            if($productModel->getTypeID()=='configurable'){

                try{
                    $priceRange=$this->_getPriceRange($productModel);
                }catch(Exception $e){
                    $priceRange='price_min="" price_max=""';
                }

            }else{
                $priceRange='price_min="" price_max=""';
            }
            
            $num_of_orders = ($orders_per_product != null && array_key_exists($prodId, $orders_per_product)) ? $orders_per_product[$prodId] : 0;

            $row='<product '.$priceRange.'  id="'.$prodId.'" type="'.$productModel->getTypeID().'" currency="'.$currency.'" visibility="'.$visibility.'" price="'.$price.'" url="'.$productUrl.'"  thumbs="'.$prodImage.'" selleable="'.$sell.'" action="insert" >';
            $row.='<description><![CDATA['.$prodDesc.']]></description>';
            $row.='<short><![CDATA['.$prodShortDesc.']]></short>';
            $row.='<name><![CDATA['.$prodName.']]></name>';
            $row.='<sku><![CDATA['.$sku.']]></sku>';
            $row.= '<purchase_popularity><![CDATA['.$num_of_orders.']]></purchase_popularity>';
            
            $row.='<product_status><![CDATA['.$product_status.']]></product_status>';

            if($useAttributes!='0'){
                foreach($attributes as $attr){
                    $action=$attr->getAttributeCode();
                    if($attr->getfrontend_input()=='select'){
                        if($productModel->getData($action)){
                            if (method_exists($productModel, 'getAttributeText')){
                                $row.='<attribute name="'.$attr->getAttributeCode().'"><![CDATA['.$productModel->getAttributeText($action).']]></attribute>';
                            } else {
                                $row.='<attribute name="'.$attr->getAttributeCode().'"><![CDATA['.$productModel->getData($action).']]></attribute>';
                            }
                        }
                    }elseif($attr->getfrontend_input()=='textarea'){
                        if($productModel->getData($action)){
                            $row.='<attribute name="'.$attr->getAttributeCode().'"><![CDATA['.$productModel->getData($action).']]></attribute>';
                        }
                    }elseif($attr->getfrontend_input()=='price'){
                        if($productModel->getData($action)){
                            $row.='<attribute name="'.$attr->getAttributeCode().'"><![CDATA['.$productModel->getData($action).']]></attribute>';
                        }
                    }elseif($attr->getfrontend_input()=='text'){
                        if($productModel->getData($action)){
                            $row.='<attribute name="'.$attr->getAttributeCode().'"><![CDATA['.$productModel->getData($action).']]></attribute>';
                        }
                    }
                }
            }
            $row.='<categories><![CDATA['.$categoriesNames.']]></categories>';

            $row.='</product>';
            $xml.=$row;
            
            if ($is_checksum && $helper){
                if ($helper->isChecksumTableExists()){
                    $checksum = $helper->calculateChecksum($productModel);
                    $helper->updateSavedProductChecksum($_tableprefix, $read, $write, $prodId, $sku, $store_id, $checksum);
                }
            }
        }

        $xml.='</catalog>';
        header('Content-type: text/xml');
        echo $xml;
        die;
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

    public function sendupdatedAction(){
        date_default_timezone_set('Asia/Jerusalem');

        set_time_limit (1800);

        $post = $this->getRequest()->getParams();

        $enabled= Mage::getStoreConfig('autocompleteplus/config/enabled');

        if($enabled=='0'){
            die('The user has disabled autocompleteplus.');
        }

        $this->imageField=Mage::getStoreConfig('autocompleteplus/config/imagefield');
        if(!$this->imageField){
            $this->imageField='thumbnail';
        }

        $this->standardImageFields=array('image','small_image','thumbnail');

        $useAttributes= Mage::getStoreConfig('autocompleteplus/config/attributes');

        $count        = $post['count'];

        $from = $post['from'];
        if(!isset($post['from'])){
            $returnArr=array(
                'status'=>'failure',
                'error_code'=>'767',
                'error_details'=>'The "from" parameter is mandatory'
            );
            echo json_encode($returnArr);
            die;
        }


        if(isset($post['to'])){
            $to   = $post['to'];
        }else{
            $to   = strtotime('now');
        }

        //$fromMysqldate = date( 'Y-m-d h:m:s', $from );
        //$toMysqldate   = date( 'Y-m-d h:m:s', $to );

        $storeQ='';

        if(isset($post['store_id'])){
            $storeQ   = 'AND store_id='.$post['store_id'];

        }


        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $_tableprefix = (string)Mage::getConfig()->getTablePrefix();

        $sql='SELECT * FROM `'.$_tableprefix.'autocompleteplus_batches` WHERE update_date BETWEEN ? AND ? '.$storeQ.' LIMIT '.$count;

        $updates=$read->fetchAll($sql,array($from,$to));

        $productScheme=Mage::getModel('catalog/product');

        if($useAttributes!='0'){
            $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($productScheme->getResource()->getTypeId())
                ->addFieldToFilter('is_user_defined', '1') // This can be changed to any attribute code
                ->load(false);
        }else{

            $attributes=null;
        }

        $mage=Mage::getVersion();
        $ext=(string) Mage::getConfig()->getNode()->modules->Autocompleteplus_Autosuggest->version;

        $xml='<?xml version="1.0"?>';
        $xml.='<catalog fromdatetime="'.$from.'" version="'.$ext.'" magento="'.$mage.'">';

        foreach ($updates as $batch) {
            if($batch['action']=='update'){
              $xml.=$this->_makeUpdateRow($batch,$attributes);
            }else{
              $xml.=$this->_makeRemoveRow($batch);
            }
        }

        $xml.='</catalog>';
        header('Content-type: text/xml');
        echo $xml;
        die;

    }

    private function _makeUpdateRow($batch,$attributes){

        $productId =         $batch['product_id'];
        $sku =               $batch['sku'];
        $storeId =           $batch['store_id'];
        $updatedate =        $batch['update_date'];
        $action =            $batch['action'];

        $currency=Mage::app()->getStore($storeId)->getCurrentCurrencyCode();

        if($productId!=null){

            $productModel=Mage::getModel('catalog/product')
                ->setStoreId($storeId)
                ->load($productId);

            if($productModel==null){
                return '';
            }

        }else{
            /*
             * FIX - Fatal error: Call to undefined method Mage_Catalog_Model_Resource_Product_Flat::loadAllAttributes()
             */
            $productModel=Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

            if($productModel==null){
                return '';
            }
            
            $productModel = $productModel->setStoreId($storeId);

            $productId=$productModel->getId();

        }

        if($productModel==null){
            return '';
        }
        
        $price       =$this->getPrice($productModel);
        $sku         =$productModel->getSku();

        $status      =$productModel->isInStock();
        $stockItem   = $productModel->getStockItem();

        $categoriesNames='';

        $categories = $productModel->getCategoryCollection()
            ->addAttributeToSelect('name');

        foreach($categories as $category) {
            $categoriesNames.=$category->getName().':'.$category->getId().';';
        }

        if($stockItem->getIsInStock()&&$status)
        {
            $sell=1;
        }else{
            $sell=0;
        }

        $productUrl       =Mage::helper('catalog/product')->getProductUrl($productId);

		$prodId           =$productModel->getId();
        $prodDesc         =$productModel->getDescription();
        $prodShortDesc    =$productModel->getShortDescription();
        $prodName         =$productModel->getName();
 
        $visibility       =$productModel->getVisibility();

        try{

            if(in_array($this->imageField,$this->standardImageFields)){
                $prodImage   =Mage::helper('catalog/image')->init($productModel, $this->imageField);
            }else{
                $function='get'.$this->imageField;
                $prodImage  =$productModel->$function();
            }

        }catch(Exception $e){
            $prodImage='';
        }

        if($productModel->getTypeID()=='configurable'){

            try{
                $priceRange=$this->_getPriceRange($productModel);
            }catch(Exception $e){
                $priceRange='price_min="" price_max=""';
            }

        }else{
            $priceRange='price_min="" price_max=""';
        }

        
		
        $row='<product '.$priceRange.' id="'.$prodId.'" type="'.$productModel->getTypeID().'" updatedate="'.$updatedate.'" currency="'.$currency.'" storeid="'.$storeId.'" visibility="'.$visibility.'" price="'.$price.'" url="'.$productUrl.'"  thumbs="'.$prodImage.'" selleable="'.$sell.'" action="'.$action.'" >';
        $row.='<description><![CDATA['.$prodDesc.']]></description>';
        $row.='<short><![CDATA['.$prodShortDesc.']]></short>';
        $row.='<name><![CDATA['.$prodName.']]></name>';
        $row.='<sku><![CDATA['.$sku.']]></sku>';

        if($attributes!=null){
            foreach($attributes as $attr){

                $action=$attr->getAttributeCode();

                if($attr->getfrontend_input()=='select'){

                    if($productModel->getData($action)){
                        $row.='<attribute attribute_type="'.$attr->getfrontend_input().'"  name="'.$attr->getAttributeCode().'"><![CDATA['.$productModel->getAttributeText($action).']]></attribute>';
                    }

                }elseif($attr->getfrontend_input()=='textarea'){

                    if($productModel->getData($action)){
                        $row.='<attribute attribute_type="'.$attr->getfrontend_input().'"  name="'.$attr->getAttributeCode().'"><![CDATA['.$productModel->getData($action).']]></attribute>';
                    }
                }elseif($attr->getfrontend_input()=='price'){

                    if($productModel->getData($action)){
                        $row.='<attribute attribute_type="'.$attr->getfrontend_input().'"  name="'.$attr->getAttributeCode().'"><![CDATA['.$productModel->getData($action).']]></attribute>';
                    }
                }elseif($attr->getfrontend_input()=='text'){

                    if($productModel->getData($action)){
                        $row.='<attribute attribute_type="'.$attr->getfrontend_input().'"  name="'.$attr->getAttributeCode().'"><![CDATA['.$productModel->getData($action).']]></attribute>';
                    }
                }elseif($attr->getfrontend_input()=='multiselect'){

                    if($productModel->getData($action)){

                        $val='';

                        foreach($productModel->getAttributeText($action) as $multiplVal){
                            $val.=$multiplVal.';';
                        }

                        $val=trim($val,';');

                        $row.='<attribute attribute_type="'.$attr->getfrontend_input().'" name="'.$attr->getAttributeCode().'"><![CDATA['.$val.']]></attribute>';
                    }

                }


            }
        }

        $row.='<categories><![CDATA['.$categoriesNames.']]></categories>';
        $row.='</product>';

        return $row;
    }

    private function _makeRemoveRow($batch){

        $updatedate = $batch['update_date'];
        $action     = $batch['action'];
        $sku        = $batch['sku'];
        $productId  = $batch['product_id'];

        $row='<product updatedate="'.$updatedate.'" action="'.$action.'" id="'.$productId.'">';
        $row.='<sku><![CDATA['.$sku.']]></sku>';
        $row.='<id><![CDATA['.$productId.']]></id>';
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

    private function __checkAccess(){

        $post = $this->getRequest()->getParams();

        $key=Mage::getModel('autocompleteplus_autosuggest/observer')->getKey();

        if(isset($post['key'])&&$post['key']==$key){
            return true;
        }else{
            return false;
        }

    }

    public function checkinstallAction(){

        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $_tableprefix = (string)Mage::getConfig()->getTablePrefix();

        $sql='SELECT * FROM `'.$_tableprefix.'autocompleteplus_config` WHERE `id` =1';

        $licenseData=$read->fetchAll($sql);

        $key=$licenseData[0]['licensekey'];

        if(strlen($key)>0&&$key!='failed'){
          echo 'the key exists';
        }else{
            echo 'no key inside';
        }

    }

    public function versAction(){
        $mage = Mage::getVersion();
        $ext = (string) Mage::getConfig()->getNode()->modules->Autocompleteplus_Autosuggest->version;
        try{
            $num_of_products = Mage::getModel('catalog/product')->getCollection()
                    ->addStoreFilter(Mage::app()->getStore()->getStoreId())
                    ->getSize();
        } catch (Exception $e){
            $num_of_products = -1;
        }
        
        if (method_exists('Mage' , 'getEdition')){
            $edition = Mage::getEdition();
        } else {
            $edition = 'Community';
        } 
        
        $helper     = Mage::helper('autocompleteplus_autosuggest');
        $uuid       = $helper->getKey();
        $site_url   = $helper->getConfigDataByFullPath('web/unsecure/base_url');
        $store_id   = Mage::app()->getStore()->getStoreId();
        
        $result = array('mage' => $mage, 
                        'ext' => $ext, 
                        'num_of_products' => $num_of_products, 
                        'edition' => $edition,
                        'uuid' => $uuid,
                        'site_url' => $site_url,
                        'store_id' => $store_id
        );
        
        $post = $this->getRequest()->getParams();  

        if (array_key_exists('modules', $post))
            $get_modules = $post['modules'];
        else 
            $get_modules = false; 
        if ($get_modules){
            try{
                $modules_array = array();
                foreach (Mage::getConfig()->getNode('modules')->children() as $name => $module) {
                    if ($module->codePool != 'core' && $module->active == 'true'){
                        $modules_array[$name] = $module;
                    }
                }
            } catch (Exception $e){
                $modules_array = array();
            }
            $result['modules'] = $modules_array;
        }
        echo json_encode($result);die;
    }
    
    public function getConflictAction(){
        $post = $this->getRequest()->getParams();
        if (array_key_exists('all', $post))
            $get_all_conflicts = $post['all'];
        else
            $get_all_conflicts = false;
        
        $helper = Mage::helper('autocompleteplus_autosuggest');
        if ($get_all_conflicts){
            $result = $helper->getExtensionConflict(true);
        }else{
            $result = $helper->getExtensionConflict();
        }
        echo json_encode($result);die;
    }

    public function getstoresAction(){

        $helper=Mage::helper('autocompleteplus_autosuggest');

        echo $helper->getMultiStoreDataJson();
        die;
    }

    public function updateemailAction(){

        $data = $this->getRequest()->getPost();

        $email=$data['email'];
        $uuid=$this->_getUUID();
        
        Mage::getModel('core/config')->saveConfig('autocompleteplus/config/store_email',$email);

        $params=array(
            'uuid'=>$uuid,
            'email'=>$email
        );

        $helper=Mage::helper('autocompleteplus_autosuggest');

        $command="http://magento.autocompleteplus.com/ext_update_email";

        $res=$helper->sendPostCurl($command,$params);

        $result=json_decode($res);

        if($result->status=='OK'){
            echo 'Your email address was updated!';
        }
    }

    public function updatesitemapAction(){

        $helper=Mage::helper('autocompleteplus_autosuggest');

        $key=$helper->getKey();

        $url=$helper->getConfigDataByFullPath('web/unsecure/base_url');

        if($key!='InstallFailedUUID' && $key!='failed'){

            $stemapUrl='Sitemap:http://magento.instantsearchplus.com/ext_sitemap?u='.$key.PHP_EOL;

            $robotsPath=Mage::getBaseDir().DS.'robots.txt';

            $write=false;

            if(file_exists($robotsPath)){
                if( strpos(file_get_contents($robotsPath),$stemapUrl) == false) {
                    $write=true;
                }
            }else{

                if(is_writable(Mage::getBaseDir())){

                    //create robots sitemap
                    file_put_contents($robotsPath,$stemapUrl);
                }else{

                    //write message that directory is not writteble
                    $command="http://magento.autocompleteplus.com/install_error";

                    $data=array();
                    $data['site']=$url;
                    $data['msg']='Directory '.Mage::getBaseDir().' is not writable.';
                    $res=$helper->sendPostCurl($command,$data);
                }
            }

            if($write){
                if(is_writable($robotsPath)){

                    //append sitemap
                    file_put_contents($robotsPath, $stemapUrl, FILE_APPEND | LOCK_EX);
                }else{
                    //write message that file is not writteble
                    $command="http://magento.autocompleteplus.com/install_error";

                    $data=array();
                    $data['site']=$url;
                    $data['msg']='File '.$robotsPath.' is not writable.';
                    $res=$helper->sendPostCurl($command,$data);
                }
            }

        }
    }

    public function updateAction(){
        set_time_limit (1800);

        $post = $this->getRequest()->getParams();

        $enabled= Mage::getStoreConfig('autocompleteplus/config/enabled');

        if($enabled=='0'){
            die('The user has disabled autocompleteplus.');
        }

        $imageField=Mage::getStoreConfig('autocompleteplus/config/imagefield');
        if(!$imageField){
            $imageField='thumbnail';
        }

        $currency=Mage::app()->getStore()->getCurrentCurrencyCode();

        $standardImageFields=array('image','small_image','thumbnail');

        $useAttributes= Mage::getStoreConfig('autocompleteplus/config/attributes');

        $startInd     = $post['offset'];
        if(!$startInd){
            $startInd=0;
        }

        $count        = $post['count'];

        //maxim products on one page is 200
        if(!$count||$count>10000){
            $count=10000;
        }
        //retrieving page number
        $pageNum=($startInd/$count)+1;

        $mage=Mage::getVersion();
        $ext=(string) Mage::getConfig()->getNode()->modules->Autocompleteplus_Autosuggest->version;

        $xml='<?xml version="1.0"?>';
        $xml.='<catalog version="'.$ext.'" magento="'.$mage.'">';


        $collection=Mage::getModel('catalog/product')->getCollection();

        if(isset($post['store'])&&$post['store']!=''){
            $collection->addStoreFilter($post['store']);
        }

        $productScheme=Mage::getModel('catalog/product');

        if($useAttributes!='0'){

            $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($productScheme->getResource()->getTypeId())
                ->addFieldToFilter('is_user_defined', '1') // This can be changed to any attribute code
                ->load(false);

        }

        //setting page+products on the page
        $collection->getSelect()->limit($count,$startInd);//->limitPage($pageNum, $count);//setPage($pageNum, $count)->load();

        $collection->load();

        $xml='<?xml version="1.0"?>';
        $xml.='<catalog>';

        foreach ($collection as $product) {

            $productCollData=$product->getData();
            $productModel=Mage::getModel('catalog/product')->load($productCollData['entity_id']);

            $categoriesNames='';

            $categories = $productModel->getCategoryCollection()
                ->addAttributeToSelect('name');

            foreach($categories as $category) {
                $categoriesNames.=$category->getName().':'.$category->getId().';';
            }

            $price       = $this->getPrice($productModel);
            $sku         = $productModel->getSku();

            $status      = $productModel->isInStock();
            $stockItem   = $productModel->getStockItem();

            if($stockItem->getIsInStock()&&$status)
            {
                $sell=1;
            }else{
                $sell=0;
            }

            $productUrl       =Mage::helper('catalog/product')->getProductUrl($productCollData['entity_id']);
            $prodId           =$productModel->getId();
            $prodDesc         =$productModel->getDescription();
            $prodShortDesc    =$productModel->getShortDescription();
            $prodName         =$productModel->getName();

            $visibility       =$productModel->getVisibility();

            try{

                if(in_array($imageField,$standardImageFields)){
                    $prodImage   =Mage::helper('catalog/image')->init($productModel, $imageField);
                }else{
                    $function='get'.$imageField;
                    $prodImage  =$productModel->$function();
                }

            }catch(Exception $e){
                $prodImage='';
            }

            if($productModel->getTypeID()=='configurable'){

                try{
                    $priceRange=$this->_getPriceRange($productModel);
                }catch(Exception $e){
                    $priceRange='price_min="" price_max=""';
                }

            }else{
                $priceRange='price_min="" price_max=""';
            }

            $row='<product '.$priceRange.' id="'.$prodId.'" type="'.$productModel->getTypeID().'"  currency="'.$currency.'" visibility="'.$visibility.'" price="'.$price.'" url="'.$productUrl.'"  thumbs="'.$prodImage.'" selleable="'.$sell.'" action="update" >';
            $row.='<description><![CDATA['.$prodDesc.']]></description>';
            $row.='<short><![CDATA['.$prodShortDesc.']]></short>';
            $row.='<name><![CDATA['.$prodName.']]></name>';
            $row.='<sku><![CDATA['.$sku.']]></sku>';

            if($useAttributes!='0'){

                foreach($attributes as $attr){

                    $action=$attr->getAttributeCode();

                      if($attr->getfrontend_input()=='select'){

                            if($productModel->getData($action)){
//                                 $row.='<attribute name="'.$attr->getAttributeCode().'"><![CDATA['.$productModel->getAttributeText($action).']]></attribute>';
                                $row.='<attribute name="'.$attr->getAttributeCode().'"><![CDATA['.$productModel->getData($action).']]></attribute>';
                            }

                        }elseif($attr->getfrontend_input()=='textarea'){

                            if($productModel->getData($action)){
                                $row.='<attribute name="'.$attr->getAttributeCode().'"><![CDATA['.$productModel->getData($action).']]></attribute>';
                            }
                        }elseif($attr->getfrontend_input()=='price'){

                            if($productModel->getData($action)){
                                $row.='<attribute name="'.$attr->getAttributeCode().'"><![CDATA['.$productModel->getData($action).']]></attribute>';
                            }
                        }elseif($attr->getfrontend_input()=='text'){

                            if($productModel->getData($action)){
                                $row.='<attribute name="'.$attr->getAttributeCode().'"><![CDATA['.$productModel->getData($action).']]></attribute>';
                            }
                        }


                }

            }
            $row.='<categories><![CDATA['.$categoriesNames.']]></categories>';

            $row.='</product>';
            $xml.=$row;
        }

        $xml.='</catalog>';
        header('Content-type: text/xml');
        echo $xml;
        die;

    }

    private function _getPriceRange($product){

        $max = '';
        $min = '';

        $pricesByAttributeValues = array();

        $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
        $basePrice = $product->getFinalPrice();
        
        $items = $attributes->getItems();
        if (is_array($items)){
            foreach ($items as $attribute){
                $prices = $attribute->getPrices();
                if (is_array($prices)){
                    foreach ($prices as $price){
                        if ($price['is_percent']){ //if the price is specified in percents
                            $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'] * $basePrice / 100;
                        }
                        else { //if the price is absolute value
                            $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'];
                        }
                    }
                }
            }
        }


        $simple = $product->getTypeInstance()->getUsedProducts();

        foreach ($simple as $sProduct){
            $totalPrice = $basePrice;

            foreach ($attributes as $attribute){

                $value = $sProduct->getData($attribute->getProductAttribute()->getAttributeCode());
                if (isset($pricesByAttributeValues[$value])){
                    $totalPrice += $pricesByAttributeValues[$value];
                }
            }
            if(!$max || $totalPrice > $max)
                $max = $totalPrice;
            if(!$min || $totalPrice < $min)
                $min = $totalPrice;
        }

        $priceRange='price_min="'.$min.'" price_max="'.$max.'"';

        return $priceRange;

    }

    protected function _getUUID(){

        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $_tableprefix = (string)Mage::getConfig()->getTablePrefix();

        $tblExist=$write->showTableStatus($_tableprefix.'autocompleteplus_config');

        if(!$tblExist){return '';}

        $sql='SELECT * FROM `'.$_tableprefix.'autocompleteplus_config` WHERE `id` =1';

        $licenseData=$read->fetchAll($sql);

        $key=$licenseData[0]['licensekey'];

        return $key;

    }

    protected function _setUUID($key){

        try{

            $_tableprefix = (string)Mage::getConfig()->getTablePrefix();

            $read = Mage::getSingleton('core/resource')->getConnection('core_read');

            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            $tblExist=$write->showTableStatus($_tableprefix.'autocompleteplus_config');

            if(!$tblExist){return;}

            $sqlFetch    ='SELECT * FROM '. $_tableprefix.'autocompleteplus_config WHERE id = 1';

            $updates=$write->fetchAll($sqlFetch);

            if($updates&&count($updates)!=0){

                $sql='UPDATE '. $_tableprefix.'autocompleteplus_config  SET licensekey=? WHERE id = 1';

                $write->query($sql, array($key));

            }else{

                $sql='INSERT INTO '. $_tableprefix.'autocompleteplus_config  (licensekey) VALUES (?)';

                $write->query($sql, array($key));

            }


        }catch(Exception $e){
            Mage::log($e->getMessage(),null,'autocompleteplus.log');
        }

    }
    
    
    private function getOrdersPerProduct($store_id, $product_id_list, $month_interval){
        if (count($product_id_list) <= 0)
            return null;
        $id_str = implode(',', $product_id_list);
        $query = Mage::getResourceModel('sales/order_item_collection');
        $select = $query->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('product_id','SUM(qty_ordered)'))
            ->where(new Zend_Db_Expr('store_id = ' . $store_id))
            ->where(new Zend_Db_Expr('product_id IN ('.$id_str.')'))
            ->where(new Zend_Db_Expr('created_at BETWEEN NOW() - INTERVAL '.$month_interval.' MONTH AND NOW()'))
            ->group(array('product_id'));
        
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $results = $readConnection->fetchAll($select);
        
        $orders_per_product = array();
        foreach ($results as $res){
            $orders_per_product[$res['product_id']] = (int)$res['SUM(qty_ordered)'];
        }
        return $orders_per_product;
    }
    
    public function getIspUuidAction(){
        echo $this->_getUUID();
    }
    
    public function setIspUuidAction(){
        $url_domain = 'http://magento.instantsearchplus.com/update_uuid';    
        $storeId = Mage::app()->getStore()->getStoreId();
        $site_url = $helper->getConfigDataByFullPath('web/unsecure/base_url');
        $url = $url_domain . '?store_id=' . $storeId . '&site_url=' . $site_url;
        
        $helper = Mage::helper('autocompleteplus_autosuggest');
        $resp = $helper->sendCurl($url);
        $response_json = json_decode($resp);
        
        if (array_key_exists('uuid', $response_json)){
            if (strlen($response_json->uuid) == 36 && substr_count($response_json->uuid, '-') == 4){
                $this->_setUUID($response_json->uuid);
            }
        }
    }
    
    public function checkDeletedAction(){
        $helper = Mage::helper('autocompleteplus_autosuggest');        
        if (!$helper->isChecksumTableExists()){
            return;
        }
        $time_stamp = time();
        
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table_prefix = (string)Mage::getConfig()->getTablePrefix();
        
        $post = $this->getRequest()->getParams();
        if (array_key_exists('store_id', $post)){
            $store_id = $post['store_id'];
        }else{
            $store_id = Mage::app()->getStore()->getStoreId();          // default
        }
        
        $sql_fetch = 'SELECT identifier FROM ' . $table_prefix . 'autocompleteplus_checksum WHERE store_id=?';       
        $updates = $read->fetchPairs($sql_fetch, array($store_id));     // empty array if fails
        if (empty($updates)){
            return;
        }
            
        $checksum_ids = array_keys($updates);   // array of all checksum table identifiers        
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addFieldToFilter('entity_id',array('in'=>$checksum_ids));
        $found_ids = $collection->getAllIds();

        $removed_products_list = array_diff($checksum_ids, $found_ids);     // list of identifiers that are not present in the store (removed at some point)
        $removed_ids = array();
        
        // removing non-existing identifiers from checksum table
        if (!empty($removed_products_list)){
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql_delete = 'DELETE FROM ' . $table_prefix . 'autocompleteplus_checksum WHERE identifier IN (' . implode(',', $removed_products_list) . ')';
            $write->query($sql_delete);

            foreach ($removed_products_list as $product_id){
                $helper->deleteProductFromTables($read, $write, $table_prefix, $product_id, $store_id);
                $removed_ids[] = $product_id;
            }
        }        
        
        $args = array('removed_ids' 		=> $removed_ids,
                      'uuid' 	            => $helper->getKey(),
                      'store_id'            => $store_id,
                      'latency'             => time() - $time_stamp,         // seconds
        );
        echo json_encode($args);    // returning the summary
    }
    
    public function checksumAction(){
        $helper = Mage::helper('autocompleteplus_autosuggest');
        $checksum_server = $helper->getServerUrl();
        if (!$helper->isChecksumTableExists()){
            $helper->ispErrorLog('checksum table not exist');
            exit(json_encode(array('status' => 'checksum table not exist')));
        }
        $max_exe_time = -1;
        
        $post = $this->getRequest()->getParams();
        if (array_key_exists('store_id', $post)){
            $store_id = $post['store_id'];
        }else{
            $store_id = Mage::app()->getStore()->getStoreId();      // default
        }
        if (array_key_exists('count', $post)){
            $count = $post['count'];
        }else{
            $count = self::MAX_NUM_OF_PRODUCTS_CHECKSUM_ITERATION;  // default
        }
        if (array_key_exists('offset', $post))
            $start_index = $post['offset'];
        else
            $start_index = 0;               // default
        if (array_key_exists('timeout', $post))
            $php_timeout = $post['timeout'];
        else
            $php_timeout = -1;              // default
        if (array_key_exists('is_single', $post))
            $is_single = $post['is_single'];
        else
            $is_single = 0;                 // default
        
        if ($count > self::MAX_NUM_OF_PRODUCTS_CHECKSUM_ITERATION && $php_timeout != -1){
            $max_exe_time = ini_get('max_execution_time');
            ini_set('max_execution_time', $php_timeout);                   // 1 hour ~ 60*60
        }
        
        $uuid = $helper->getKey();
        $site_url = $helper->getConfigDataByFullPath('web/unsecure/base_url');
        
        $collection = Mage::getModel('catalog/product')->getCollection();
        if ($store_id){
            $collection->addStoreFilter($store_id);
        }
        $num_of_products = $collection->getSize();
        
        if ($count + $start_index > $num_of_products){
            $count = $num_of_products - $start_index;
        }
        
        // sending log to the server        
        $log_msg = 'Update checksum is starting...';
        $log_msg .= (' number of products in this store: ' . $num_of_products . ' | from: ' . $start_index . ', to: ' . ($start_index + $count));
        $server_url = $checksum_server . '/magento_logging_record';
        $request = $server_url . '?uuid=' . $uuid . '&site_url=' . $site_url . '&msg=' . urlencode($log_msg);
        if ($store_id)
            $request .= '&store_id=' . $store_id;
        $resp = $helper->sendCurl($request);
                                   
        $start_time = time();
        $num_of_updated_checksum = 0;
        if($count > self::MAX_NUM_OF_PRODUCTS_CHECKSUM_ITERATION){
            $iter = $start_index;
            while ($iter < $count){
                // start updating the checksum table if needed
                $num_of_updated_checksum += $helper->compareProductsChecksum($iter, self::MAX_NUM_OF_PRODUCTS_CHECKSUM_ITERATION, $store_id);
                $iter += self::MAX_NUM_OF_PRODUCTS_CHECKSUM_ITERATION;
            }
        } else {
            // start updating the checksum table if needed
            $num_of_updated_checksum = $helper->compareProductsChecksum($start_index, $count, $store_id);
        }
        
        $process_time = time() - $start_time;
        // sending confirmation/summary to the server
        $args = array(  'uuid' 			      => $uuid,
                        'site_url' 			  => $site_url,
                        'store_id'            => $store_id,
                        'updated_checksum' 	  => $num_of_updated_checksum,
                        'total_checksum' 	  => $count,
                        'num_of_products'     => $num_of_products,
                        'start_index'		  => $start_index,
                        'end_index'	          => $start_index + $count,
                        'count'               => $count,
                        'ext_version'	      => (string)Mage::getConfig()->getNode()->modules->Autocompleteplus_Autosuggest->version,
                        'mage_version'        => Mage::getVersion(),
                        'latency'             => $process_time,         // seconds
        );
        if ($is_single)
            $args['is_single'] = 1;
        echo json_encode($args);    // returning the summary
        
        $resp = $helper->sendCurl($checksum_server . '/magento_checksum_iterator?' . http_build_query($args));

        if ($max_exe_time != -1){   // restore php max execution time
            ini_set('max_execution_time', $max_exe_time);
        }
    }
    
    public function connectionAction(){
        exit('1');
    } 
    
}
