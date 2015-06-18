<?php
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()->newTable($installer->getTable('autocompleteplus_autosuggest/pusher'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ), 'Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'unsigned' => true
    ))
    ->addColumn('to_send', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'unsigned' => true
    ), 'Amount left to send')
    ->addColumn('offset', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'unsigned' => true
    ))
    ->addColumn('total_batches', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'unsigned' => true
    ))
    ->addColumn('batch_number', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'unsigned' => true
    ))
    ->setComment('Keeping auth key and left to send count');

if ($installer->getConnection()->isTableExists($table->getName())) {
    $installer->getConnection()->dropTable($table->getName());
}

$installer->getConnection()->createTable($table);
$installer->endSetup();

// Checking config table values
if ($installer->getConnection()->isTableExists($this->getTable('autocompleteplus_config'))) {
    $config_arr = Mage::getModel('autocompleteplus_autosuggest/config')->getCollection()->getData();
    $config = $config_arr[0];
} else {
    $config = false;
}
$need_to_rebuild_config = true;
if ($config && isset($config['authkey']) && $config['authkey'] != ''){
    $need_to_rebuild_config == false;        // has authentication key in the table with a valid value
}

$helper = Mage::helper('autocompleteplus_autosuggest');

//getting site url
$url = $helper->getConfigDataByFullPath('web/unsecure/base_url');

//getting site owner email
$storeMail = $helper->getConfigDataByFullPath('autocompleteplus/config/store_email');

//getting site design theme package name
$package = $helper->getConfigDataByFullPath('design/package/name');

$collection = Mage::getModel('catalog/product')->getCollection();
//$productCount=$collection->count();

$multistoreJson = $helper->getMultiStoreDataJson();

$data = array();
if ($config && isset($config['licensekey'])) {
    $data['uuid'] = $config['licensekey'];
}

try {

    $commandOrig = "http://0-1vk.acp-magento.appspot.com/install";

    $data['multistore'] = $multistoreJson;
    if (method_exists('Mage' , 'getEdition')){
        $data['edition'] = Mage::getEdition();
    } else {
        $data['edition'] = 'Community';
    }
    $data['site'] = $url;
    $data['email'] = $storeMail;
    $data['f'] = '2.0.5.4';


    $auto_arr = json_decode($helper->sendPostCurl($commandOrig, $data), true);
    $key = $auto_arr['uuid'];
    $auth_key = $auto_arr['authentication_key'];

    if (strlen($key) > 50) {
        $key = 'InstallFailedUUID';
    }

    Mage::log(print_r($key, true), null, 'autocomplete.log');

    $errMsg = '';
    if ($key == 'InstallFailedUUID') {
        $errMsg .= 'Could not get license string.';
    }

    if ($package == 'base') {
        $errMsg .= ';The Admin needs to move autocomplete template files to his template folder';
    }

    if ($errMsg != '') {

        $command = "http://0-1vk.acp-magento.appspot.com/install_error";
        $data = array();
        $data['site'] = $url;
        $data['msg'] = $errMsg;
        $data['email'] = $storeMail;
        //$data['product_count']=$productCount;
        $data['multistore'] = $multistoreJson;
        $data['f'] = '2.0.5.4';
        $res = $helper->sendPostCurl($command, $data);
    }

} catch (Exception $e) {

    $key = 'failed';
    $errMsg = $e->getMessage();

    Mage::log('Install failed with a message: ' . $errMsg, null, 'autocomplete.log');

    $command = "http://0-1vk.acp-magento.appspot.com/install_error";

    $data = array();
    $data['site'] = $url;
    $data['msg'] = $errMsg;
    $data['original_install_URL'] = $commandOrig;
    $data['f'] = '2.0.5.4';
    $res = $helper->sendPostCurl($command, $data);
}


$installer->startSetup();
    
if (!$installer->getConnection()->isTableExists($this->getTable('autocompleteplus_config')) || $need_to_rebuild_config) {   // autocompleteplus_config not exists, creating new one

    $res=$installer->run("
DROP TABLE IF EXISTS {$this->getTable('autocompleteplus_config')};

CREATE TABLE IF NOT EXISTS {$this->getTable('autocompleteplus_config')} (

  `id` int(11) NOT NULL auto_increment,

  `licensekey` varchar(255) character set utf8 NOT NULL,

  `authkey` varchar(255) character set utf8 NOT NULL,

  `site_url` varchar(255) character set utf8 NOT NULL,

   PRIMARY KEY  (`id`)

) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
");

    $res = $installer->run("INSERT INTO {$this->getTable('autocompleteplus_config')} (licensekey,authkey,site_url) VALUES('" . $key . "','" . $auth_key . "','" . $url . "');");
} else {      
    // table autocompleteplus_config exists, updating its fields
    $res=$installer->run("UPDATE {$this->getTable('autocompleteplus_config')} SET licensekey='" . $key . "', authkey='" . $auth_key . "' WHERE id=1;");
}

$installer->endSetup();
