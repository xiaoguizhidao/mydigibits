<?php
class EM_Blog_Block_Adminhtml_Post_Edit_Tab_Tag extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('post_form', array('legend'=>Mage::helper('blog')->__('Post information')));
      $tag = $fieldset->addField('tag', 'text', array(
          'label'     => Mage::helper('blog')->__('Tags'),
          'name'      => 'tag',
      ));
      /*$fieldset->addField('add_tag', 'button', array(
          'label'     => Mage::helper('blog')->__('Add Tag'),
          'name'      => 'add_tag',
          'style'     => 'width:100px',   
      ));*/
      
      $html = '<button class="button" id="add_tag" onclick="updateTag();return false;"><span><span>'.$this->__('Add Tag').'</span></span></button>
                <div id="tag-indicator" class="autocomplete"></div>
                                 <span id="tag_auto_indicator" class="autocomplete-indicator" style="display: none"></span>';
      $tagListHtml    = '<div><ul id="taglist">';//</ul></div>';
      $tagValueHtml   = '<div id="tag-value">';//</div>';
      $tagNameHtml    = '<div id="tag-name">';//</div>';
                
      if ( Mage::getSingleton('adminhtml/session')->getPostData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPostData());
          Mage::getSingleton('adminhtml/session')->setPostData(null);
      } elseif ( $data = Mage::registry('post_data') ) {
          $form->setValues(Mage::registry('post_data')->getData());
          if(isset($data['tags']))
          {
                $i = 0;
                foreach($data['tags'] as $t)
                {
                    $tagListHtml    .= "<li style='cursor:pointer' onclick='removeTag(".$i.");' id='".$i."'>".$t->getName()."</li>";
                    $tagValueHtml   .= "<input id='".$i."value' type='hidden' name='tags[]' value='".$t->getId()."'/>";
                    $tagNameHtml    .= "<input id='".$i."name' type='hidden' name='tags_name[]' value='".$t->getName()."'/>";
                    $i++;
                }
          }
      }
      $tagListHtml  .= "</ul></div>";
      $tagValueHtml .= "</div>";
      $tagNameHtml  .= "</div>";
      $html .=$tagListHtml.$tagValueHtml.$tagNameHtml;
      $tag->setAfterElementHtml($html);
      //print_r(Mage::registry('post_data')->getData());exit;
      return parent::_prepareForm();
  }
}