<?php

class EM_Blog_Adminhtml_TagController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('blog/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
	
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$modelTag  = Mage::getModel('blog/tag');
		$model = $modelTag->load($id);
	
		if ($model->getId() || $id == 0) {
            
            //print_r($model['_data']);exit;
          
		  
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			//print_r($data);exit;
			if (!empty($data)) {
			  //echo 'abc';exit;
				$model->setData($data);
			}

			Mage::register('tag_data', $model);
                
			$this->loadLayout();
			$this->_setActiveMenu('blog/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('blog/adminhtml_tag_edit'))
				->_addLeft($this->getLayout()->createBlock('blog/adminhtml_tag_edit_tabs'));

			$this->renderLayout();
			//echo 'abc';exit;
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
            Mage::register('tag_data', Mage::getModel('blog/tag'));

            $this->loadLayout();
            $this->_setActiveMenu('blog/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('blog/adminhtml_tag_edit'))
                    ->_addLeft($this->getLayout()->createBlock('blog/adminhtml_tag_edit_tabs'));

            $this->renderLayout();
	}

	
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			$model = Mage::getModel('blog/tag');
			if(!$data['custom_design_from'])
                    $data['custom_design_from'] = NULL;
                if(!$data['custom_design_to'])
                    $data['custom_design_to'] = NULL;
			
			if(!$data['tag_identifier'])
            {
                 $data['tag_identifier'] = Mage::helper('blog/post')->friendlyURL($data['name']);
            }
            else
                 $data['tag_identifier'] = Mage::helper('blog/post')->friendlyURL($data['tag_identifier']);
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				
				    $id = $model->save()->saveUrlRewrite();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('blog')->__('Tag was successfully saved'));
				
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
                $model = Mage::getModel('blog/tag');
                 
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
        $tagIds = $this->getRequest()->getParam('tag');
        if(!is_array($tagIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($tagIds as $tagId) {
                    $blog = Mage::getModel('blog/tag')->load($tagId);
                    $blog->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($tagIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
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
	
    public function massStatusAction()
    {
        $tagIds = $this->getRequest()->getParam('tag');
        if(!is_array($tagIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($tagIds as $tagId) {
                    $blog = Mage::getSingleton('blog/tag')
                        ->load($tagId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($tagIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'tag.csv';
        $content    = $this->getLayout()->createBlock('blog/adminhtml_tag_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'tag.xml';
        $content    = $this->getLayout()->createBlock('blog/adminhtml_tag_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function validateAction()
    {
        $url = $this->getRequest()->getPost('tag_identifier');
        if(!$url)
             $url = Mage::helper('blog/post')->friendlyURL($this->getRequest()->getPost('name'));
        else
             $url = Mage::helper('blog/post')->friendlyURL($url);

        $response = new Varien_Object();
        $response->setError(false);
        $urlInstance = Mage::getModel('blog/url');
        if($urlInstance->validate('tag/'.$url.'.html','tag_id',$this->getRequest()->getParam('id'))){

            $response->setError(true);
            $response->setAttribute("Tag Identifier");
            $response->setMessage("Tag identifier is unique");
            $response->setData('identifier',$url);
        }
        $this->getResponse()->setBody($response->toJson());
	}
}
