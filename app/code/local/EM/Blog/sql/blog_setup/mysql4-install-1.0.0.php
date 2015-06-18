<?php
/* @var $installer EM_Blog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$installer->run("
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS {$this->getTable('blog/category_post')};
DROP TABLE IF EXISTS {$installer->getTable('blog/category_post_index')};
DROP TABLE IF EXISTS {$this->getTable('blog/post_entity_varchar')};
DROP TABLE IF EXISTS {$this->getTable('blog/post_entity_int')};
DROP TABLE IF EXISTS {$this->getTable('blog/post_entity_text')};
DROP TABLE IF EXISTS {$this->getTable('blog/post_entity_datetime')};
DROP TABLE IF EXISTS {$this->getTable('blog/post_link')};
DROP TABLE IF EXISTS {$this->getTable('blog/post_link_type')};
DROP TABLE IF EXISTS {$this->getTable('blog/post_link_attribute_int')};
DROP TABLE IF EXISTS {$this->getTable('blog/post_link_attribute')};
DROP TABLE IF EXISTS {$this->getTable('blog/post_link_attribute')};
DROP TABLE IF EXISTS {$this->getTable('blog/url')};
DROP TABLE IF EXISTS {$this->getTable('blog/tag_post')};
DROP TABLE IF EXISTS {$this->getTable('blog/comment')};
DROP TABLE IF EXISTS {$this->getTable('blog/post')};
DROP TABLE IF EXISTS {$this->getTable('blog/category_entity_varchar')};
DROP TABLE IF EXISTS {$this->getTable('blog/category_entity_int')};
DROP TABLE IF EXISTS {$this->getTable('blog/category_entity_text')};
DROP TABLE IF EXISTS {$this->getTable('blog/category_entity_datetime')};
DROP TABLE IF EXISTS {$this->getTable('blog/category')};
DROP TABLE IF EXISTS {$this->getTable('blog/eav_attribute')};
DROP TABLE IF EXISTS {$this->getTable('blog/tag')};
");
/* Create table 'blog/post' */
$table = $installer->getConnection()
    ->newTable($installer->getTable('blog/post'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Entity ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Type Id')
	->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created Date')
	->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Updated Date')		
    ->setComment('Event Entity Table');
$installer->getConnection()->createTable($table);

/* Create table 'blog/post_entity_varchar' */
$table = $installer->getConnection()
    ->newTable($installer->getTable(array('blog/post', 'varchar')))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Value Id')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Type Id')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Attribute Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Id')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            array('blog/post', 'varchar'),
            array('entity_id', 'attribute_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('entity_id', 'attribute_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName(array('blog/post', 'varchar'), array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName(array('blog/post', 'varchar'), array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName(array('blog/post', 'varchar'), array('entity_id')),
        array('entity_id'))
    ->addForeignKey(
        $installer->getFkName(array('blog/post', 'varchar'), 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/post', 'varchar'), 'entity_id', 'blog/post', 'entity_id'),
        'entity_id', $installer->getTable('blog/post'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/post', 'varchar'), 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Post Entity Varchar');
$installer->getConnection()->createTable($table);

/* Create table 'blog/post_entity_int' */
$table = $installer->getConnection()
    ->newTable($installer->getTable(array('blog/post', 'int')))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Value Id')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Type Id')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Attribute Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Id')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            array('blog/post', 'int'),
            array('entity_id', 'attribute_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('entity_id', 'attribute_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName(array('blog/post', 'int'), array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName(array('blog/post', 'int'), array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName(array('blog/post', 'int'), array('entity_id')),
        array('entity_id'))
    ->addForeignKey(
        $installer->getFkName(array('blog/post', 'int'), 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/post', 'int'), 'entity_id', 'blog/post', 'entity_id'),
        'entity_id', $installer->getTable('blog/post'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/post', 'int'), 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Post Entity Int');
$installer->getConnection()->createTable($table);
	
/* Create table 'blog/post_entity_text' */
$table = $installer->getConnection()
    ->newTable($installer->getTable(array('blog/post', 'text')))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Value Id')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Type Id')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Attribute Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Id')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            array('blog/post', 'text'),
            array('entity_id', 'attribute_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('entity_id', 'attribute_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName(array('blog/post', 'text'), array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName(array('blog/post', 'text'), array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName(array('blog/post', 'text'), array('entity_id')),
        array('entity_id'))
    ->addForeignKey(
        $installer->getFkName(array('blog/post', 'text'), 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/post', 'text'), 'entity_id', 'blog/post', 'entity_id'),
        'entity_id', $installer->getTable('blog/post'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/post', 'text'), 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Post Entity Text');
$installer->getConnection()->createTable($table);	

/* Create table 'blog/post_entity_datetime' */
$table = $installer->getConnection()
    ->newTable($installer->getTable(array('blog/post', 'datetime')))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Value Id')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Type Id')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Attribute Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Id')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            array('blog/post', 'datetime'),
            array('entity_id', 'attribute_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('entity_id', 'attribute_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName(array('blog/post', 'datetime'), array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName(array('blog/post', 'datetime'), array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName(array('blog/post', 'datetime'), array('entity_id')),
        array('entity_id'))
    ->addForeignKey(
        $installer->getFkName(array('blog/post', 'datetime'), 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/post', 'datetime'), 'entity_id', 'blog/post', 'entity_id'),
        'entity_id', $installer->getTable('blog/post'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/post', 'datetime'), 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Post Entity Datetime');
$installer->getConnection()->createTable($table);

/**
 * Create table 'blog/category'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('blog/category'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Type ID')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Parent Category ID')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Creation Time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Update Time')
    ->addColumn('path', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Tree Path')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Position')
    ->addColumn('level', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Tree Level')
    ->addColumn('children_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Child Count')
    ->addIndex($installer->getIdxName('blog/category', array('level')),
        array('level'))
    ->setComment('Blog Category Table');
$installer->getConnection()->createTable($table);

/**
 * Create table array('blog/category', 'datetime')
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable(array('blog/category','datetime')))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Value ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Type ID')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Attribute ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            array('blog/category', 'datetime'),
            array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName(array('blog/category', 'datetime'), array('entity_id')),
        array('entity_id'))
    ->addIndex($installer->getIdxName(array('blog/category', 'datetime'), array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName(array('blog/category', 'datetime'), array('store_id')),
        array('store_id'))
    ->addForeignKey(
        $installer->getFkName(array('blog/category', 'datetime'), 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/category', 'datetime'), 'entity_id', 'blog/category', 'entity_id'),
        'entity_id', $installer->getTable('blog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/category', 'datetime'), 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Blog Category Datetime Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table array('blog/category', 'int')
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable(array('blog/category', 'int')))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Value ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Type ID')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Attribute ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            array('blog/category', 'int'),
            array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName(array('blog/category', 'int'), array('entity_id')),
        array('entity_id'))
    ->addIndex($installer->getIdxName(array('blog/category', 'int'), array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName(array('blog/category', 'int'), array('store_id')),
        array('store_id'))
    ->addForeignKey(
        $installer->getFkName(array('blog/category', 'int'), 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/category', 'int'), 'entity_id', 'blog/category', 'entity_id'),
        'entity_id', $installer->getTable('blog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/category', 'int'), 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Blog Category Integer Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table array('blog/category', 'text')
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable(array('blog/category', 'text')))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Value ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Type ID')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Attribute ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            array('blog/category', 'text'),
            array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName(array('blog/category', 'text'), array('entity_id')),
        array('entity_id'))
    ->addIndex($installer->getIdxName(array('blog/category', 'text'), array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName(array('blog/category', 'text'), array('store_id')),
        array('store_id'))
    ->addForeignKey(
        $installer->getFkName(array('blog/category', 'text'), 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/category', 'text'), 'entity_id', 'blog/category', 'entity_id'),
        'entity_id', $installer->getTable('blog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/category', 'text'), 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Blog Category Text Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table array('blog/category', 'varchar')
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable(array('blog/category', 'varchar')))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Value ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity Type ID')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Attribute ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            array('blog/category', 'varchar'),
            array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName(array('blog/category', 'varchar'), array('entity_id')),
        array('entity_id'))
    ->addIndex($installer->getIdxName(array('blog/category', 'varchar'), array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName(array('blog/category', 'varchar'), array('store_id')),
        array('store_id'))
    ->addForeignKey(
        $installer->getFkName(array('blog/category', 'varchar'), 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/category', 'varchar'), 'entity_id', 'blog/category', 'entity_id'),
        'entity_id', $installer->getTable('blog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('blog/category', 'varchar'), 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Blog Category Varchar Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'blog/category_post'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('blog/category_post'))
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Category ID')
    ->addColumn('post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Post ID')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Position')
    ->addIndex($installer->getIdxName('blog/category_post', array('post_id')),
        array('post_id'))
    ->addForeignKey($installer->getFkName('blog/category_post', 'category_id', 'blog/category', 'entity_id'),
        'category_id', $installer->getTable('blog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('blog/category_post', 'post_id', 'blog/post', 'entity_id'),
        'post_id', $installer->getTable('blog/post'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Blog Post To Category Linkage Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'blog/category_post_index'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('blog/category_post_index'))
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Category ID')
    ->addColumn('post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Post ID')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Position')
    ->addColumn('is_parent', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Parent')
    ->addIndex(
        $installer->getIdxName(
            'blog/category_post_index',
            array('post_id', 'category_id')
        ),
        array('post_id', 'category_id'))
    ->addIndex(
        $installer->getIdxName(
            'blog/category_post_index',
            array('category_id', 'is_parent', 'position')
        ),
        array('category_id', 'is_parent', 'position'))
    ->addForeignKey(
        $installer->getFkName('blog/category_post_index', 'category_id', 'blog/category', 'entity_id'),
        'category_id', $installer->getTable('blog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('blog/category_post_index', 'post_id', 'blog/post', 'entity_id'),
        'post_id', $installer->getTable('blog/post'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Blog Category Post Index');
$installer->getConnection()->createTable($table);

/**
 * Create table 'blog/post_link_type'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('blog/post_link_type'))
    ->addColumn('link_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Link Type ID')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => true,
        'default'   => null,
        ), 'Code')
    ->setComment('Blog Post Link Type Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'blog/post_link'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('blog/post_link'))
    ->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Link ID')
    ->addColumn('post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Post ID')
    ->addColumn('linked_post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Linked Post ID')
    ->addColumn('link_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Link Type ID')
    ->addIndex(
        $installer->getIdxName(
            'blog/post_link',
            array('link_type_id', 'post_id', 'linked_post_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('link_type_id', 'post_id', 'linked_post_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('blog/post_link', array('post_id')),
        array('post_id'))
    ->addIndex($installer->getIdxName('blog/post_link', array('linked_post_id')),
        array('linked_post_id'))
    ->addIndex($installer->getIdxName('blog/post_link', array('link_type_id')),
        array('link_type_id'))
    ->addForeignKey(
        $installer->getFkName('blog/post_link', 'linked_post_id', 'blog/post', 'entity_id'),
        'linked_post_id', $installer->getTable('blog/post'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('blog/post_link', 'post_id', 'blog/post', 'entity_id'),
        'post_id', $installer->getTable('blog/post'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('blog/post_link', 'link_type_id', 'blog/post_link_type', 'link_type_id'),
        'link_type_id', $installer->getTable('blog/post_link_type'), 'link_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Blog Post To Post Linkage Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'blog/post_link_attribute'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('blog/post_link_attribute'))
    ->addColumn('post_link_attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Post Link Attribute ID')
    ->addColumn('link_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Link Type ID')
    ->addColumn('post_link_attribute_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => true,
        'default'   => null,
        ), 'Post Link Attribute Code')
    ->addColumn('data_type', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => true,
        'default'   => null,
        ), 'Data Type')
    ->addIndex($installer->getIdxName('blog/post_link_attribute', array('link_type_id')),
        array('link_type_id'))
    ->addForeignKey(
        $installer->getFkName(
            'blog/post_link_attribute',
            'link_type_id',
            'blog/post_link_type',
            'link_type_id'
        ),
        'link_type_id', $installer->getTable('blog/post_link_type'), 'link_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Blog Post Link Attribute Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'blog/post_link_attribute_int'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('blog/post_link_attribute_int'))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Value ID')
    ->addColumn('post_link_attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Post Link Attribute ID')
    ->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true,
        ), 'Link ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Value')
    ->addIndex($installer->getIdxName('blog/post_link_attribute_int', array('post_link_attribute_id')),
        array('post_link_attribute_id'))
    ->addIndex($installer->getIdxName('blog/post_link_attribute_int', array('link_id')),
        array('link_id'))
    ->addIndex(
        $installer->getIdxName(
            'blog/post_link_attribute_int',
            array('post_link_attribute_id', 'link_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('post_link_attribute_id', 'link_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey(
        $installer->getFkName(
            'blog/post_link_attribute_int',
            'link_id',
            'blog/post_link',
            'link_id'
        ),
        'link_id', $installer->getTable('blog/post_link'), 'link_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'blog/post_link_attribute_int',
            'post_link_attribute_id',
            'blog/post_link_attribute',
            'post_link_attribute_id'
        ),
        'post_link_attribute_id', $installer->getTable('blog/post_link_attribute'),
        'post_link_attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Blog Post Link Integer Attribute Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'blog/eav_attribute'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('blog/eav_attribute'))
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Attribute ID')
    ->addColumn('is_global', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        ), 'Is Global')
    ->addColumn('is_visible', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        ), 'Is Visible')
    ->addColumn('is_searchable', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Searchable')
    ->addColumn('is_filterable', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Filterable')
    ->addColumn('is_comparable', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Comparable')
    ->addColumn('is_visible_on_front', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Visible On Front')
    ->addColumn('is_html_allowed_on_front', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is HTML Allowed On Front')
    ->addColumn('is_used_for_price_rules', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Used For Price Rules')
    ->addColumn('is_filterable_in_search', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Filterable In Search')
    ->addColumn('used_in_product_listing', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Used In Product Listing')
    ->addColumn('used_for_sort_by', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Used For Sorting')
    ->addColumn('is_configurable', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        ), 'Is Configurable')
    ->addColumn('apply_to', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        ), 'Apply To')
    ->addColumn('is_visible_in_advanced_search', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Visible In Advanced Search')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Position')
    ->addColumn('is_wysiwyg_enabled', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is WYSIWYG Enabled')
    ->addColumn('is_used_for_promo_rules', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Used For Promo Rules')
    ->addIndex($installer->getIdxName('blog/eav_attribute', array('used_for_sort_by')),
        array('used_for_sort_by'))
    ->addIndex($installer->getIdxName('blog/eav_attribute', array('used_in_product_listing')),
        array('used_in_product_listing'))
    ->addForeignKey($installer->getFkName('blog/eav_attribute', 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Blog EAV Attribute Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'blog/tag'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('blog/tag'))
	 ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'unsigned'  => true,
        'nullable'  => false
        ), 'Name')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false
        ), 'Status - 0:approved,1:pending')
	->addColumn('custom_design', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Custom Design')	
    ->addColumn('custom_design_from', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        ), 'Custom Design From')
	->addColumn('custom_design_to', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        ), 'Custom Design To')
	->addColumn('custom_layout', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false
        ), 'Custom Layout')
	->addColumn('custom_layout_update_xml', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => false
        ), 'Custom Layout Update Xml')		
	->addColumn('tag_identifier', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false
        ), 'Tag Identifier')		
    ->setComment('Blog Tag Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'blog/tag_post'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('blog/tag_post'))
    ->addColumn('tag_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true
        ), 'Category ID')
    ->addColumn('post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true
        ), 'Post ID')
    ->addForeignKey($installer->getFkName('blog/tag_post', 'tag_id', 'blog/tag', 'id'),
        'tag_id', $installer->getTable('blog/tag'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('blog/tag_post', 'post_id', 'blog/post', 'entity_id'),
        'post_id', $installer->getTable('blog/post'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Blog Tag To Post Linkage Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'blog/comment'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('blog/comment'))
	 ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'ID')
    ->addColumn('comment_content', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'unsigned'  => true,
        'nullable'  => false
        ), 'Name')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true
        ), 'Parent Id')
	->addColumn('time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Time')	
    ->addColumn('post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false
        ), 'Post Id')
	 ->addColumn('status_comment', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false
        ), '0: disable , 1 : pending , 2 : approved')	
	->addColumn('username', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false
        ), 'Username')
	->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false
        ), 'Email')
	->addForeignKey($installer->getFkName('blog/comment', 'post_id', 'blog/post', 'entity_id'),
        'post_id', $installer->getTable('blog/post'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Blog Comment Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'blog/url'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('blog/url'))
	 ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'ID')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
		'default'	=> NULL
        ), 'Category ID')
    ->addColumn('post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
		'default'	=> NULL
        ), 'Post ID')
	->addColumn('tag_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
		'default'	=> NULL
        ), 'Tag ID')	
    ->addColumn('request_path', Varien_Db_Ddl_Table::TYPE_TEXT, 555, array(
        'nullable'  => false
        ), 'Request Path')
    ->addIndex($installer->getIdxName('blog/url', array('post_id')),
        array('post_id'))
    ->addForeignKey($installer->getFkName('blog/url', 'category_id', 'blog/category', 'entity_id'),
        'category_id', $installer->getTable('blog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('blog/url', 'post_id', 'blog/post', 'entity_id'),
        'post_id', $installer->getTable('blog/post'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->addForeignKey($installer->getFkName('blog/url', 'tag_id', 'blog/tag', 'id'),
        'tag_id', $installer->getTable('blog/tag'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)	
    ->setComment('Blog Url Rewrite Table');
$installer->getConnection()->createTable($table);

$installer->installEntities(); 
$installer->rebuildCategoryLevels();
$this->convertOldTreeToNew();
$installer->run("
	INSERT INTO {$this->getTable('blog/post_link_type')} (`link_type_id`, `code`) VALUES
(NULL, 'relation');
	INSERT INTO {$this->getTable('blog/post_link_attribute')} (`post_link_attribute_id`, `link_type_id`, `post_link_attribute_code`, `data_type`) VALUES
(NULL, 1, 'position', 'int');
");
$installer->endSetup(); 
/* Insert Root Cateogry */
$category = Mage::getModel('blog/category');
$categoryTypeId = $category->getResource()->getEntityType()->getEntityTypeId();
$data = array(
	'entity_type_id' => $categoryTypeId,
	'name'	=>	Mage::helper('blog')->__('Blog Root Category'),
	'page_title'	=> Mage::helper('blog')->__('Blog Root Category'),
	'parent_id'=>1,
	'is_active'=>1,
	'created_at'=>Varien_Date::now(),
	'updated_at'=>Varien_Date::now(),
	'path'	=>	'1',
	'position'	=>	2,
	'children_count' =>	0
);
$category->addData($data)->save();

