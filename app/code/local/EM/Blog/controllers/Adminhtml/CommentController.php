<?php

class EM_Blog_Adminhtml_CommentController extends Mage_Adminhtml_Controller_action
{
	protected $flag = 0 ;
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('blog/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Comments Manager'), Mage::helper('adminhtml')->__('Comments Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
			
	}

	public function editAction() {
	
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('blog/comment')->load($id);
       
		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('comment_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('blog/comment_manager');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Comment Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('blog/adminhtml_comment_edit'))
				->_addLeft($this->getLayout()->createBlock('blog/adminhtml_comment_edit_tabs'));

			$this->renderLayout();
			
			//echo 'abc';exit;
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
            $this->flag = 1;
            Mage::register('comment_data', Mage::getModel('blog/comment'));

            $this->loadLayout();
            $this->_setActiveMenu('blog/comment_manager');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('blog/adminhtml_comment_edit'))
                    ->_addLeft($this->getLayout()->createBlock('blog/adminhtml_comment_edit_tabs'));

            $this->renderLayout();

	}
 
	public function saveAction() {
            if ($data = $this->getRequest()->getPost()) {
                $model = Mage::getModel('blog/comment');

                $model->setData($data)
                        ->setId($this->getRequest()->getParam('id'));

                try {
                        $model->save();

                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('blog')->__('Item was successfully saved'));
                        /*if($this->flag==1)
                        {
                                $idp = $this->getRequest()->getParam('id');
                                $write = Mage::getSingleton('core/resource')->getConnection('core_write');
                        $query = "update ".Mage::getSingleton('core/resource')->getTableName('blog_post')." set comment_count = comment_count + 1 where id=$idp";
                        $write->query($query);
                        $this->flag = 0 ;
                        }*/

                        Mage::getSingleton('adminhtml/session')->setFormData(false);

                        if ($this->getRequest()->getParam('back')) {
                                $this->_redirect('*/*/edit', array('id' => $model->getId()));

                                return;
                        }
                        $this->_redirect('*/*/');
                        return;
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    Mage::getSingleton('adminhtml/session')->setFormData($data);
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }
            }
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Unable to find item to save'));
            $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('blog/comment');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
    	
        $postIds = $this->getRequest()->getParam('comment');
        //print_r($postIds);die('1111');
        if(!is_array($postIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($postIds as $postId) {
                    $blog = Mage::getModel('blog/comment')->load($postId);
                    $blog->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($postIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
    	
    	
        $postIds = $this->getRequest()->getParam('comment');
        
        if(!is_array($postIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($postIds as $postId) {
                    $blog = Mage::getSingleton('blog/comment')
                        ->load($postId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($postIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'comment.csv';
        $content    = $this->getLayout()->createBlock('blog/adminhtml_comment_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'comment.xml';
        $content    = $this->getLayout()->createBlock('blog/adminhtml_comment_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}