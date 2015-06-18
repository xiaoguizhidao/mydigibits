<?php

class EM_Blog_Block_Adminhtml_Post_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'blog';
        $this->_controller = 'adminhtml_post';
        $id = Mage::app()->getFrontController()->getRequest()->getParam('id',0);
        $this->setValidationUrl(Mage::helper('adminhtml')->getUrl('blog/adminhtml_post/validate',array('id'=>$id)));
        //echo $this->getJsUrl('tiny_mce/tiny_mce.js');exit;
        $this->_updateButton('save', 'label', Mage::helper('blog')->__('Save Post'));
        $this->_updateButton('delete', 'label', Mage::helper('blog')->__('Delete Post'));
		//$this->getTagList();
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $tagList = $this->getTagList();
        $urlTagList = Mage::helper('adminhtml')->getUrl('blog/adminhtml_post/autotag');
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('post_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'post_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'post_content');
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
                            $('post_identifier').value = response.identifier;
                            $('post_identifier').up().insert(div);
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
            
            new Ajax.Autocompleter(
                    'tag',
                    'tag-indicator',
                    '$urlTagList',
                    {
                        paramName:'q',
                        minChars:3,
                        indicator:'tag_auto_indicator',
                        updateElement:getSelectionTag,
                        evalJSON:'force',
                    }
                );
            function getSelectionTag(li)
            {
                var tagid = li.getAttribute('value');
                var tagname = li.getAttribute('tag');
                addTag(tagname,tagid);
                //alert('abc');
            }
            
            function addTag(tagname,tagid)
            {
                var curtags = $('taglist').innerHTML;
                var curtagsvalue = $('tag-value').innerHTML;
                var curtagsname = $('tag-name').innerHTML;
                if(!curtags)
                {
                    $('taglist').innerHTML = curtags + '<li style=\"cursor:pointer\" id=\"0\" onclick=\"removeTag(0);\">' + tagname + '</li>';
                    $('tag-value').innerHTML = curtagsvalue + '<input type=\"hidden\" id=\"0value\" name=\"tags[]\" value=\"' + tagid + '\"/>';
                    $('tag-name').innerHTML = curtagsname + '<input type=\"hidden\" id=\"0name\" name=\"tags_name[]\" value=\"' + tagname + '\"/>';
                }
                    
                else
                {
                    var list = $('taglist').getElementsByTagName('li');
                    var i;
                    for(i=0;i<list.length;i++)
                    {
                        if(list[i].innerHTML == tagname)
                            return;
                    }
                    $('taglist').innerHTML = curtags + '<li style=\"cursor:pointer\" id=\"' + i + '\" onclick=\"removeTag(' + i + ');\">' + tagname + '</li>';
                    $('tag-value').innerHTML = curtagsvalue + '<input id=\"' + i + 'value\" type=\"hidden\" name=\"tags[]\" value=\"' + tagid + '\"/>';
                    $('tag-name').innerHTML = curtagsname + '<input id=\"' + i + 'name\" type=\"hidden\" name=\"tags_name[]\" value=\"' + tagname + '\"/>';
                }
                $('tag').setValue('');
            }
            
            function removeTag(id)
            {
                //alert($(id));
                var tag = document.getElementById(id);
                var tagValue = document.getElementById(id + 'value');
                var tagName = document.getElementById(id + 'name');
                tag.parentNode.removeChild(tag);
                tagValue.parentNode.removeChild(tagValue);
                tagName.parentNode.removeChild(tagName);
                //alert('abc');
                
            }

            function updateTag()
            {
                var new_tag = $('tag').value;
                if(new_tag == '')
                    alert('not value');
                else
                    addTag(new_tag,0);
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
        if( Mage::registry('post_data') && Mage::registry('post_data')->getId() ) {
            return Mage::helper('blog')->__("Edit Post '%s'", $this->htmlEscape(Mage::registry('post_data')->getTitle()));
        } else {
            return Mage::helper('blog')->__('Add Post');
        }
    }
    
    public function getTagList()
    {
        $model = Mage::getModel('blog/post');
        return json_encode($model->getTags());
    }
}