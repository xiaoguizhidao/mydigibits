<?php

class EM_Blog_Block_Adminhtml_Tag_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'blog';
        $this->_controller = 'adminhtml_tag';
        $id = Mage::app()->getFrontController()->getRequest()->getParam('id',0);
        $this->setValidationUrl(Mage::helper('adminhtml')->getUrl('blog/adminhtml_tag/validate',array('id'=>$id)));
        $this->_updateButton('save', 'label', Mage::helper('blog')->__('Save Tag'));
        $this->_updateButton('delete', 'label', Mage::helper('blog')->__('Delete Tag'));
		//$this->getTagList();
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        //print_r($skinUrl);exit;
        
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('tag_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'tag_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'tag_content');
                }
            }

            editForm._processValidationResult = function(transport) {
                var response = transport.responseText.evalJSON();
                if (response.error){
                    if (response.attribute && $(response.attribute)) {
                        $(response.attribute).setHasError(true, editForm);
                        Validation.ajaxError($(response.attribute), response.message);
                        if (!Prototype.Browser.IE){
                            $(response.attribute).focus();
                        }
                    }
                    else if ($('messages')) {
                      if(!$('advice-validate-ajax-sku'))
                        {
                            var div = new Element('div', { 'class': 'validation-advice', id: 'advice-validate-ajax-sku' }).update(response.message);
                            $('tag_identifier').value = response.identifier;
                            $('tag_identifier').up().insert(div);
                        }

                    }
                }
                else{
                    editForm._submit();
                }
            };

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
                 
        ";
        /*             window.addEvent('domready', function() {

                  new Autocompleter.Request.JSON('tag', '$urlTagList', {
                      'postVar': 'tag'
                  });
              
              });*/
        //$this->getChildHtml('js');
    }

    public function getHeaderText()
    {
        if( Mage::registry('tag_data') && Mage::registry('tag_data')->getId() ) {
            return Mage::helper('blog')->__("Edit Tag '%s'", $this->htmlEscape(Mage::registry('tag_data')->getName()));
        } else {
            return Mage::helper('blog')->__('Add Tag');
        }
    }
    
    public function getTagList()
    {
        $model = Mage::getModel('blog/tag');
        return json_encode($model->getTags());
    }
}
