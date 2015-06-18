<?php
class EM_Blog_Helper_Post extends Mage_Core_Helper_Abstract {

	/**
	 * Renders CMS page
	 *
	 * Call from controller action
	 *
	 * @param Mage_Core_Controller_Front_Action $action
	 * @param integer $pageId
	 * @return boolean
	 */
	/*public function renderPage(Mage_Core_Controller_Front_Action $action, $identifier=null) {

		$page = Mage::getModel('blog/post');
		if (!is_null($identifier) && $identifier!==$page->getId()) {
			$page->setStoreId(Mage::app()->getStore()->getId());
			if (!$page->load($identifier)) {
				return false;
			}
		}

		if (!$page->getId()) {
			return false;
		}
		if ($page->getStatus() == 2) {
			return false;
		}
		$page_title = Mage::getSingleton('blog/post')->load($identifier)->getTitle();
		$blog_title = Mage::getStoreConfig('blog/blog/title') . " - ";

		$action->loadLayout();
		if ($storage = Mage::getSingleton('customer/session')) {
			$action->getLayout()->getMessagesBlock()->addMessages($storage->getMessages(true));
		}
		$action->getLayout()->getBlock('head')->setTitle($blog_title . $page_title);
		$action->getLayout()->getBlock('root')->setTemplate(Mage::getStoreConfig('blog/blog/layout'));
		$action->renderLayout();

		return true;
	}

	public function closetags($html) {
		#put all opened tags into an array
		preg_match_all ( "#<([a-z]+)( .*)?(?!/)>#iU", $html, $result );
		$openedtags = $result[1];

		#put all closed tags into an array
		preg_match_all ( "#</([a-z]+)>#iU", $html, $result );
		$closedtags = $result[1];
		$len_opened = count ( $openedtags );
		# all tags are closed
		if( count ( $closedtags ) == $len_opened ) {
			return $html;
		}
		$openedtags = array_reverse ( $openedtags );
		# close tags
		for( $i = 0; $i < $len_opened; $i++ ) {
			if ( !in_array ( $openedtags[$i], $closedtags ) ) {
				$html .= "</" . $openedtags[$i] . ">";
			}
			else {
				unset ( $closedtags[array_search ( $openedtags[$i], $closedtags)] );
			}
		}
		return $html;
	}*/
	
    function khongdau($str) {

           $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
      
           $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
      
           $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
      
           $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
      
           $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
      
           $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
      
           $str = preg_replace("/(đ)/", 'd', $str);
      
           
      
           $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
      
           $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
      
           $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
      
           $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
      
           $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
      
           $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
      
           $str = preg_replace("/(Đ)/", 'D', $str);
      
           //$str = str_replace(" ", "-", str_replace("&*#39;","",$str));
      
           return $str;

     }
     
     function friendlyURL($string){
            $string = str_replace("$","",htmlspecialchars($string));
            $string = $this->khongdau($string);   
            $string = preg_replace("`\[.*$%\]`U","",$string);
            $string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$string);
            $string = htmlentities($string, ENT_COMPAT, 'utf-8');
            $string = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $string );
            $string = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "-", $string);
            return strtolower(trim($string, '-'));
     }  
}