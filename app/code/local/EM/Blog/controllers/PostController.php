<?php
include_once("recaptchalib.php");
class EM_Blog_PostController extends Mage_Core_Controller_Front_Action
{
	/**
     * Initialize requested post object
     *
     * @return EM_Blog_Model_Post
     */
	public function _initPost()
    {
        
        $postId = (int) $this->getRequest()->getParam('id', false);
        if (!$postId) {
            return false;
        }

        $post = Mage::getModel('blog/post')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($postId);
		if(!$post->getStatus())
			return false;
		Mage::register('current_post',$post);
        return $post;
    }
	
    /**
     * Post view action
     */
    public function viewAction()
    {
    	if($post = $this->_initPost()){
			$date = $post->getCustomDesignDate();
			if (array_key_exists('from', $date) && array_key_exists('to', $date)
				&& Mage::app()->getLocale()->isStoreDateInInterval(null, $date['from'], $date['to'])
			){ 
				//========= set theme ===========
				Mage::helper('blog')->setTheme($post->getData('custom_design'), $post->getData('custom_layout_update_xml'), $post->getData('custom_layout'),$this);
				//====================================
				$title = $this->getLayout()->getBlock('head')->getTitle();
				$this->getLayout()->getBlock('head')->setTitle($title." ".$post->getData('title'));
			}
			else
			{
				$this->loadLayout();
				$title = $this->getLayout()->getBlock('head')->getTitle();
				$this->getLayout()->getBlock('head')->setTitle($title." ".$post->getData('title')); 
			}

			$keywords = $post->getData('post_meta_keywords');
			if(!$keywords)
			{
				$catId = $this->getRequest()->getParam('cat_id');
				if($catId)
				{
					$category = Mage::getModel('blog/category')->load($catId);
					$keywords = $category->getMetaKeywords();
				}

			}
			if($keywords)
				$this->getLayout()->getBlock('head')->setKeywords($keywords);
			$description = $post->getData('post_meta_description');
			if(!$description)
			{
				if(!$category)
				{
					$catId = $this->getRequest()->getParam('cat_id');
					if($catId)
					{
						$category = Mage::getModel('blog/category')->load($catId);
						$description = $category->getMetaDescription();
					}
				}
				else
				{
					$description = $category->getMetaDescription();
				}
			}
			if($description)
				$this->getLayout()->getBlock('head')->setDescription($description);
			$this->renderLayout();		
		}
		elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
		}
    }
	
    public function checkRecaptcha()
    {
        
        $privatekey = Mage::getStoreConfig('blog/recaptcha/private_key');
        if ($this->getRequest()->getPost("recaptcha_response_field")) {
            $resp = recaptcha_check_answer ($privatekey,
                                                $_SERVER["REMOTE_ADDR"],
                                                $_POST["recaptcha_challenge_field"],
                                                $_POST["recaptcha_response_field"]);
            //header('Content-Type: text/html; charset=utf-8');
            return $resp->is_valid;
        }
    }
    
    public function newcommentAction()
    {
        if(Mage::getStoreConfig('blog/recaptcha/enable_recapcha'))
            $recaptcha = $this->checkRecaptcha();
        else
            $recaptcha = 1;
        if($recaptcha == 1)
        {
            $data = $this->getRequest()->getPost();
            unset($data['submit']);
            $data['time'] = date('o-m-d H:i:s');
            if(Mage::helper('customer')->isLoggedIn())//user login
            {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $data['username'] = $customer->getName();
                $data['email'] = $customer->getEmail();
            }

            if(Mage::getStoreConfig('blog/comments/auto_approved'))
                $data['status_comment'] = 2;
            elseif(Mage::getStoreConfig('blog/comments/auto_approved_login') && Mage::helper('customer')->isLoggedIn())
                $data['status_comment'] = 2;

            $data['comment_content'] = nl2br(htmlspecialchars($data['comment_content']));
            $model = Mage::getModel('blog/comment')->setData($data);

            try {
                            //$write = Mage::getSingleton('core/resource')->getConnection('core_write');
                            //$query = "SET FOREIGN_KEY_CHECKS=0;";
                            //$write->query($query);

                $insertId = $model->save()->getId();
                              //echo $insertId;
				if($data['parent_id'] == 0){
					$data['parent_id'] = $insertId;
					$model = Mage::getModel('blog/comment')->load($insertId)->addData($data);
					try {
						$model->setId($insertId)->save();
					} catch (Exception $e){
					}
				}
			} catch (Exception $e){
				echo $e->getMessage();
            }


              /////Send Mail for this Comment/////////

              if (Mage::getStoreConfig('blog/comments/recipient_email') != null && isset($insertId)) {
                  $translate = Mage::getSingleton('core/translate');
                    /* @var $translate Mage_Core_Model_Translate */
                    $translate->setTranslateInline(false);
                    try {
                            $data["url"] = Mage::getBaseUrl().trim($data['uri'],'/');
                            $postObject = new Varien_Object();
                            $postObject->setData($data);
                            $mailTemplate = Mage::getModel('core/email_template');
                            /* @var $mailTemplate Mage_Core_Model_Email_Template */
                            $mailTemplate->sendTransactional(
                                            Mage::getStoreConfig('blog/comments/email_template'),
                                            Mage::getStoreConfig('blog/comments/sender_email_identity'),
                                            Mage::getStoreConfig('blog/comments/recipient_email'),
                                            null,
                                            array('data' => $postObject)
                            );
                            $translate->setTranslateInline(true);
                    } catch (Exception $e) {
                            $translate->setTranslateInline(true);
                    }
                }
                /////Send Mail for this Comment/////////


                  echo "1";
        }
        else
                echo "0";
		
        
        exit;
        
        //$this->_redirect('*/*/view', array('id' => $data['post_id']));
        //print_r($data);exit;
    }
	
	
}
