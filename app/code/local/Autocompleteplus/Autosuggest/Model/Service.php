<?php

class Autocompleteplus_Autosuggest_Model_Service
{
    public function toSend() {
        // Check if Pusher is set
        $pusher = Mage::getModel('autocompleteplus_autosuggest/pusher')->getCollection()->getData();
        if (empty($pusher)) {
            $all_stores = Mage::app()->getStores();
            foreach ($all_stores as $i => $store) {
                $id = Mage::app()->getStore($i)->getId();
                $products = count(Mage::getModel('catalog/product')->getCollection()->addStoreFilter($id));
                $products = $products > 1000 ? 1000 : $products; // 1000 limit
                $total_batches = $products / 200;
                $rnd = intval($total_batches);
                $total_batches = $total_batches > $rnd ? ++$rnd : $rnd;
                $to_push = Mage::getModel('autocompleteplus_autosuggest/pusher');
                $to_push->setData('store_id', $id);
                $to_push->setData('to_send', $products);
                $to_push->setData('offset', 0);
                $to_push->setData('batch_number', 1);
                $to_push->setData('total_batches', $total_batches);
                $to_push->save();
            }
            $pusher = Mage::getModel('autocompleteplus_autosuggest/pusher')->getCollection()->getData();
        }
        // Sorting pusher array
        if (!empty($pusher)){
            //usort($pusher, '$this->compare_id');
            usort($pusher, array($this, 'compare_id'));
        }

        // Count total batches
        $total_batches = 0;
        foreach ($pusher as $push) {
            $total_batches += $push['total_batches'];
        }
        // Getting auth key
        $config_arr = Mage::getModel('autocompleteplus_autosuggest/config')->getCollection()->getData();
        $config = $config_arr[0];
        $auth_key = $config['authkey'];
        $uuid = $config['licensekey'];
        
        // Calculating store id to send
        $batch_number = 0;
        $row_batch_num = 0;
        $to_send = 0;
        $store_id = 0;
        $row_id = 0;
        foreach ($pusher as $push) {
            $to_send = $push['to_send'];
            $row_id = $push['id'];
            $batch_number += $push['batch_number']; // Calculating overall batch number
            if (!$to_send) {
                continue; // This one is done, go next
            } else {
                $offset = $push['offset'];
                $store_id = $push['store_id'];
                $row_batch_num = $push['batch_number']; // This one is per row and increments by 1
                break; // Got one that isn't finished yet, everything is set, roll out!
            }
        }
        // Sending!
        if ($to_send) {
            $count = $to_send > 200 ? 200 : $to_send;
            $helper=Mage::helper('autocompleteplus_autosuggest');
            //getting site url
            $url=$helper->getConfigDataByFullPath('web/unsecure/base_url');
            // Getting XML
            $sender = $url . "index.php/autocompleteplus/products/send";
            $s_data['offset'] = $offset;
            $s_data['count'] = $count;
            $s_data['store'] = $store_id;

            $res1 = $helper->sendPostCurl($sender, $s_data);

            // setting post data and command url
            $data['uuid'] = $uuid;
            $data['site_url'] = $url;
            $data['store_id'] = $store_id;
            $data['authentication_key'] = $auth_key;
            $data['total_batches'] = $total_batches;
            $data['batch_number'] = $batch_number;
            if ($batch_number == $total_batches) {
                $data['is_last'] = 1;
                // error_log('IS LAST ' . $data['is_last']);
            }
            $data['products'] =  $res1;

            $cmd_url = 'http://0-1vk.acp-magento.appspot.com/magento_fetch_products';

            // sending products
            $res2 = $helper->sendPostCurl($cmd_url, $data);

            // updating pusher table
            $to_send -= $count;
            $to_send = $to_send < 0 ? 0 : $to_send;
            $row_batch_num = $to_send == 0 ? $row_batch_num : ++$row_batch_num;
            $offset += $count;
            $id = $row_id;
            $to_save = array('to_send' => $to_send,'offset' => $offset, 'batch_number' => $row_batch_num);
            $model = Mage::getModel('autocompleteplus_autosuggest/pusher')->load($id)->addData($to_save);
            try {
                $model->setId($id)->save();
            } catch (Exception $e){
            }
        } else { // Dismissing the cron
            $config_xml_path = Mage::getModuleDir('etc', 'Autocompleteplus_Autosuggest') . '/config.xml';
            $config_xml = simplexml_load_file($config_xml_path) or die("Error: Cannot create object");
            if (isset($config_xml) && isset($config_xml->crontab)) {
                unset($config_xml->crontab);
                $config_xml->asXML($config_xml_path);
            }
            // Cleaning
            Mage::app()->cleanCache();
            $schedule = Mage::getModel('cron/schedule');
            $sch_col = $schedule->getCollection()
                ->addFilter('job_code', 'autocompleteplus_autosuggest_toSend');
            foreach ($sch_col as $s) {
                $s->delete();
            }
        }
    }

    private function compare_id($a, $b)
    {
        if ($a['id'] == $b['id']) return 0;
        return ($a['id'] < $b['id']) ? -1 : 1;
    }
}
