<?php
class EM_Blog_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{
     /**
     * Retreive default entities: post
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        $entities = array(
            'blog_post'    => array(
                'entity_model'            => 'blog/post',
				'attribute_model'		  => 'blog/attribute',
                'table'                          => 'blog/post',
				'additional_attribute_table'     => 'blog/eav_attribute',
				'entity_attribute_collection'    => 'blog/attribute_collection',
				'default_group'                  => 'General Information',
                'attributes'                     => array(
                    'title'          => array(
                        'type'               => 'varchar',
                        'label'              => 'Title',
                        'input'              => 'text',
                        'required'           => true,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 1
                    ),
					'post_identifier'=> array(
                        'type'               => 'varchar',
                        'label'              => 'Identifier',
                        'input'              => 'text',
                        'required'           => false,
						'global'             => EM_Blog_Model_Attribute::SCOPE_GLOBAL,
                        'sort_order'         => 2
                    ),
					'image'          => array(
                        'type'               => 'varchar',
                        'label'              => 'Image',
                        'input'              => 'image',
                        'backend'            => 'blog/post_attribute_backend_image',
                        'required'           => false,
                        'sort_order'         => 3,
                        'global'             => EM_Blog_Model_Attribute::SCOPE_STORE
                    ),
					'author_id'      => array(
                        'type'               => 'int',
                        'label'              => 'Author',
                        'input'              => 'select',
						'source'             => 'blog/post_attribute_source_author',
                        'required'           => true,
						'global'             => EM_Blog_Model_Attribute::SCOPE_GLOBAL,
                        'sort_order'         => 4
                    ),
					'status'         => array(
                        'type'               => 'int',
                        'label'              => 'Is Active',
                        'input'              => 'select',
						'source'             => 'eav/entity_attribute_source_boolean',
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'required'           => true,
                        'sort_order'         => 6
                    ),
                    'allow_comment'         => array(
                        'type'               => 'int',
                        'label'              => 'Allow Comment',
                        'input'              => 'select',
						'source'             => 'blog/post_attribute_source_allowcomment',
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'required'           => false,
                        'sort_order'         => 7
                    ),
					'post_content_heading'         => array(
                        'type'               => 'varchar',
                        'label'              => 'Post Content Heading',
                        'input'              => 'text',
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'required'           => true,
                        'sort_order'         => 8
                    ),
                    'post_intro'         => array(
                        'type'               => 'text',
                        'label'              => 'Introduction',
                        'input'              => 'editor',
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'required'           => true,
                        'sort_order'         => 9                        
                    ),
                    'post_content'         => array(
                        'type'               => 'text',
                        'label'              => 'Content',
                        'input'              => 'editor',
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'required'           => true,
                        'sort_order'         => 10
                    ),
					'custom_design'         => array(
                        'type'               => 'varchar',
                        'label'              => 'Custom Design',
                        'input'              => 'select',
						'source'             => 'core/design_source_design',
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'required'           => false,
                        'sort_order'         => 11
                    ),
					'custom_design_from'    => array(
                        'type'               => 'datetime',
                        'label'              => 'Custom Design From',
                        'input'              => 'date',
                        'backend'            => 'eav/entity_attribute_backend_datetime',
                        'required'           => false,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 12
                    ),
					'custom_design_to'    => array(
                        'type'               => 'datetime',
                        'label'              => 'Custom Design To',
                        'input'              => 'date',
                        'backend'            => 'eav/entity_attribute_backend_datetime',
                        'required'           => false,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 13
                    ),
					'custom_layout'         => array(
                        'type'               => 'varchar',
                        'label'              => 'Custom Layout',
                        'input'              => 'select',
						'source'             => 'blog/post_attribute_source_layout',
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'required'           => false,
                        'sort_order'         => 14
                    ),
                    'custom_layout_update_xml'=> array(
                        'type'               => 'text',
                        'label'              => 'Custom Layout Update Xml',
                        'input'              => 'textarea',
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'required'           => false,
                        'sort_order'         => 15                        
                    ),
                    'post_meta_keywords' => array(
                        'type'               => 'text',
                        'label'              => 'Keywords',
                        'input'              => 'textarea',
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'required'           => false,
                        'sort_order'         => 16                        
                    ),
                    'post_meta_description' => array(
                        'type'               => 'text',
                        'label'              => 'Descrition',
                        'input'              => 'textarea',
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'required'           => false,
                        'sort_order'         => 17                        
                    ),
                    'created_at'         => array(
                        'type'               => 'static',
                        'input'              => 'text',
                        'backend'            => 'eav/entity_attribute_backend_time_created',
                        'sort_order'         => 18,
                        'visible'            => false,
                    ),
                    'updated_at'         => array(
                        'type'               => 'static',
                        'input'              => 'text',
                        'backend'            => 'eav/entity_attribute_backend_time_updated',
                        'sort_order'         => 19,
                        'visible'            => false,
                    )					
                )
            ),
			EM_Blog_Model_Category::ENTITY	=>	array(
				'entity_model'            => 'blog/category',
				'attribute_model'		  => 'blog/attribute',
                'table'                          => 'blog/category',
				'additional_attribute_table'     => 'blog/eav_attribute',
				'entity_attribute_collection'    => 'blog/attribute_collection',
				'default_group'                  => 'General Information',
                'attributes'                     => array(
                    'name'          => array(
                        'type'               => 'varchar',
                        'label'              => 'Name',
                        'input'              => 'text',
                        'required'           => true,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 1
                    ),
					'description'          => array(
                        'type'               => 'text',
                        'label'              => 'Description',
                        'input'              => 'textarea',
                        'required'           => false,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 2
                    ),
					'image'          => array(
                        'type'               => 'varchar',
                        'label'              => 'Image',
                        'input'              => 'image',
                        'backend'            => 'blog/category_attribute_backend_image',
						'required'           => false,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 3
                    ),
					'page_title'          => array(
                        'type'               => 'varchar',
                        'label'              => 'Page Title',
                        'input'              => 'text',
						'required'           => true,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 4
                    ),
					'meta_keywords'          => array(
                        'type'               => 'text',
                        'label'              => 'Meta Keywords',
                        'input'              => 'textarea',
						'required'           => false,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 5
                    ),
					'meta_description'          => array(
                        'type'               => 'text',
                        'label'              => 'Meta Description',
                        'input'              => 'textarea',
						'required'           => false,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 6
                    ),
					'is_active'          => array(
                        'type'               => 'int',
                        'label'              => 'Is Active',
                        'input'              => 'select',
						'source'             => 'eav/entity_attribute_source_boolean',
						'required'           => true,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 7
                    ),
					'url_key'          => array(
                        'type'               => 'varchar',
                        'label'              => 'Url Key',
                        'input'              => 'text',
						'required'           => false,
						'global'             => EM_Blog_Model_Attribute::SCOPE_GLOBAL,
                        'sort_order'         => 8
                    ),
					'show_image'          => array(
                        'type'               => 'int',
                        'label'              => 'Show image at frontend',
                        'input'              => 'select',
						'source'             => 'eav/entity_attribute_source_boolean',
						'required'           => true,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 9
                    ),
					'display_mode'          => array(
                        'type'               => 'int',
                        'label'              => 'Display Mode',
                        'input'              => 'select',
						'source'             => 'blog/category_attribute_source_displaymode',
						'required'           => false,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 10
                    ),
					'cms_block'          => array(
                        'type'               => 'int',
                        'label'              => 'CMS Block',
                        'input'              => 'select',
						'source'             => 'blog/category_attribute_source_cmsblock',
						'required'           => false,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 11
                    ),
					'is_anchor'          => array(
                        'type'               => 'int',
                        'label'              => 'Is Anchor',
                        'input'              => 'select',
                        'source'             => 'eav/entity_attribute_source_boolean',
                        'required'           => false,
						'global'			 => EM_Blog_Model_Attribute::SCOPE_GLOBAL,
                        'sort_order'         => 12
                    ),
                    'custom_use_parent_settings' => array(
                        'type'                   => 'int',
                        'label'                  => 'Use Parent Category Settings',
                        'input'                  => 'select',
                        'source'                 => 'eav/entity_attribute_source_boolean',
                        'required'               => false,
                        'sort_order'             => 13,
                        'global'                 => EM_Blog_Model_Attribute::SCOPE_STORE
                    ),
					'custom_design'         => array(
                        'type'               => 'varchar',
                        'label'              => 'Custom Design',
                        'input'              => 'select',
						'source'             => 'core/design_source_design',
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'required'           => false,
                        'sort_order'         => 14
                    ),
					'custom_design_from'    => array(
                        'type'               => 'datetime',
                        'label'              => 'Custom Design From',
                        'input'              => 'date',
                        'backend'            => 'eav/entity_attribute_backend_datetime',
                        'required'           => false,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 15
                    ),
					'custom_design_to'    => array(
                        'type'               => 'datetime',
                        'label'              => 'Custom Design To',
                        'input'              => 'date',
                        'backend'            => 'eav/entity_attribute_backend_datetime',
                        'required'           => false,
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'sort_order'         => 16
                    ),
					'custom_layout'         => array(
                        'type'               => 'varchar',
                        'label'              => 'Custom Layout',
                        'input'              => 'select',
						'source'             => 'blog/post_attribute_source_layout',
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'required'           => false,
                        'sort_order'         => 17
                    ),
                    'custom_layout_update_xml'=> array(
                        'type'               => 'text',
                        'label'              => 'Custom Layout Update Xml',
                        'input'              => 'textarea',
						'global'             => EM_Blog_Model_Attribute::SCOPE_STORE,
                        'required'           => false,
                        'sort_order'         => 18                        
                    ),
                    'created_at'         => array(
                        'type'               => 'static',
                        'input'              => 'text',
                        'backend'            => 'eav/entity_attribute_backend_time_created',
                        'sort_order'         => 19,
                        'visible'            => false,
						'required'           => false
                    ),
                    'updated_at'         => array(
                        'type'               => 'static',
                        'input'              => 'text',
                        'backend'            => 'eav/entity_attribute_backend_time_updated',
                        'sort_order'         => 20,
                        'visible'            => false,
						'required'           => false
                    ),
                    'level'              => array(
                        'type'               => 'static',
                        'label'              => 'Level',
                        'required'           => false,
                        'sort_order'         => 21,
                        'visible'            => false,
                        'group'              => 'General Information',
                    ),
                    'children_count'     => array(
                        'type'               => 'static',
                        'label'              => 'Children Count',
                        'required'           => false,
                        'sort_order'         => 22,
                        'visible'            => false,
                        'group'              => 'General Information',
                    ),
                    'path'               => array(
                        'type'               => 'static',
                        'label'              => 'Path',
                        'required'           => false,
                        'sort_order'         => 23,
                        'visible'            => false,
                        'group'              => 'General Information',
                    ),
                    'position'           => array(
                        'type'               => 'static',
                        'label'              => 'Position',
                        'required'           => false,
                        'sort_order'         => 24,
                        'visible'            => false,
                        'group'              => 'General Information',
                    )
				)	
			)
        );
        return $entities;
    }
	
	/**
     * Converts old tree to new
     *
     * @deprecated since 1.5.0.0
     * @return EM_Blog_Model_Resource_Setup
     */
    public function convertOldTreeToNew()
    {
        if (!Mage::getModel('blog/category')->load(1)->getId()) {
            Mage::getModel('blog/category')->setId(1)->setPath(1)->save();
        }

        $categories = array();

        $select = $this->getConnection()->select();
        $select->from($this->getTable('blog/category'));
        $categories = $this->getConnection()->fetchAll($select);

        if (is_array($categories)) {
            foreach ($categories as $category) {
                $path = $this->_getCategoryPath($category);
                $path = array_reverse($path);
                $path = implode('/', $path);
                if ($category['entity_id'] != 1 && substr($path, 0, 2) != '1/') {
                    $path = "1/{$path}";
                }

                $this
                    ->getConnection()
                    ->update(
                        $this->getTable('blog/category'),
                        array('path' => $path),
                        array('entity_id = ?' => $category['entity_id'])
                    );
            }
        }
        return $this;
    }

    /**
     * Returns category entity row by category id
     *
     * @param int $entityId
     * @return array
     */
    protected function _getCategoryEntityRow($entityId)
    {
        $select = $this->getConnection()->select();

        $select->from($this->getTable('blog/category'));
        $select->where('entity_id = :entity_id');

        return $this->getConnection()->fetchRow($select, array('entity_id' => $entityId));
    }

    /**
     * Returns category path as array
     *
     * @param array $category
     * @param array $path
     * @return string
     */
    protected function _getCategoryPath($category, $path = array())
    {
        $path[] = $category['entity_id'];

        if ($category['parent_id'] != 0) {
            $parentCategory = $this->_getCategoryEntityRow($category['parent_id']);
            if ($parentCategory) {
                $path = $this->_getCategoryPath($parentCategory, $path);
            }
        }

        return $path;
    }

    /**
     * Creates level values for categories and saves them
     *
     * @deprecated since 1.5.0.0
     * @return Mage_Catalog_Model_Resource_Setup
     */
    public function rebuildCategoryLevels()
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getTable('blog/category'));

        $categories = $adapter->fetchAll($select);

        foreach ($categories as $category) {
            $level = count(explode('/', $category['path']))-1;
            $adapter->update(
                $this->getTable('blog/category'),
                array('level' => $level),
                array('entity_id = ?' => $category['entity_id'])
            );
        }
        return $this;
    }
}
