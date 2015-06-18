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

$helper=Mage::helper('autocompleteplus_autosuggest');

$key=$helper->getKey();

$url=$helper->getConfigDataByFullPath('web/unsecure/base_url');
Mage::log('mysql4-upgrade-2.0.2.8-2.0.3.0.php',null,'autocompleteplus.log');

if($key!='InstallFailedUUID' && $key!='failed'){

    $stemapUrl='Sitemap:http://magento.instantsearchplus.com/ext_sitemap?u='.$key.PHP_EOL;

    $robotsPath=Mage::getBaseDir().DS.'robots.txt';

    if (file_exists($robotsPath)) {

        if(is_writable($robotsPath)){

          //append sitemap
            file_put_contents($robotsPath, $stemapUrl, FILE_APPEND | LOCK_EX);
        }else{
            //write message that file is not writteble
            $command="http://0-1vk.acp-magento.appspot.com/install_error";

            $data=array();
            $data['site']=$url;
            $data['msg']='File '.$robotsPath.' is not writable.';
            $data['f'] = '28';
            $res=$helper->sendPostCurl($command,$data);
        }

    }else{
        //create file
        if(is_writable(Mage::getBaseDir())){

            //create robots sitemap
            file_put_contents($robotsPath,$stemapUrl);
        }else{

            //write message that directory is not writteble
            $command="http://0-1vk.acp-magento.appspot.com/install_error";

            $data=array();
            $data['site']=$url;
            $data['msg']='Directory '.Mage::getBaseDir().' is not writable.';
            $data['f'] = '28';
            $res=$helper->sendPostCurl($command,$data);
        }
    }
}