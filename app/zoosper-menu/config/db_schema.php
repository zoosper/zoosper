<?php

declare(strict_types=1);

return array (
  'tables' => 
  array (
    'menus' => 
    array (
      'columns' => 
      array (
        'id' => 
        array (
          'type' => 'integer',
          'primary' => true,
          'auto_increment' => true,
        ),
        'site_id' => 
        array (
          'type' => 'integer',
          'nullable' => false,
        ),
        'code' => 
        array (
          'type' => 'string',
          'length' => 120,
          'nullable' => false,
        ),
        'label' => 
        array (
          'type' => 'string',
          'length' => 190,
          'nullable' => false,
        ),
        'status' => 
        array (
          'type' => 'string',
          'length' => 32,
          'nullable' => false,
          'default' => 'active',
        ),
        'created_at' => 
        array (
          'type' => 'datetime',
          'nullable' => false,
        ),
        'updated_at' => 
        array (
          'type' => 'datetime',
          'nullable' => false,
        ),
      ),
      'indexes' => 
      array (
        'uniq_menus_site_code' => 
        array (
          'columns' => 
          array (
            0 => 'site_id',
            1 => 'code',
          ),
          'unique' => true,
        ),
        'idx_menus_site_status' => 
        array (
          'columns' => 
          array (
            0 => 'site_id',
            1 => 'status',
          ),
        ),
      ),
      'foreign_keys' => 
      array (
        'fk_menus_site' => 
        array (
          'columns' => 
          array (
            0 => 'site_id',
          ),
          'referenced_table' => 'sites',
          'referenced_columns' => 
          array (
            0 => 'id',
          ),
          'on_delete' => 'CASCADE',
        ),
      ),
    ),
    'menu_items' => 
    array (
      'columns' => 
      array (
        'id' => 
        array (
          'type' => 'integer',
          'primary' => true,
          'auto_increment' => true,
        ),
        'menu_id' => 
        array (
          'type' => 'integer',
          'nullable' => false,
        ),
        'parent_id' => 
        array (
          'type' => 'integer',
          'nullable' => true,
        ),
        'page_id' => 
        array (
          'type' => 'integer',
          'nullable' => true,
        ),
        'label' => 
        array (
          'type' => 'string',
          'length' => 190,
          'nullable' => false,
        ),
        'url' => 
        array (
          'type' => 'string',
          'length' => 2048,
          'nullable' => true,
        ),
        'target' => 
        array (
          'type' => 'string',
          'length' => 16,
          'nullable' => false,
          'default' => '_self',
        ),
        'position' => 
        array (
          'type' => 'integer',
          'nullable' => false,
          'default' => 0,
        ),
        'status' => 
        array (
          'type' => 'string',
          'length' => 32,
          'nullable' => false,
          'default' => 'active',
        ),
        'created_at' => 
        array (
          'type' => 'datetime',
          'nullable' => false,
        ),
        'updated_at' => 
        array (
          'type' => 'datetime',
          'nullable' => false,
        ),
      ),
      'indexes' => 
      array (
        'idx_menu_items_menu_parent_position' => 
        array (
          'columns' => 
          array (
            0 => 'menu_id',
            1 => 'parent_id',
            2 => 'position',
          ),
        ),
        'idx_menu_items_page' => 
        array (
          'columns' => 
          array (
            0 => 'page_id',
          ),
        ),
      ),
      'foreign_keys' => 
      array (
        'fk_menu_items_menu' => 
        array (
          'columns' => 
          array (
            0 => 'menu_id',
          ),
          'referenced_table' => 'menus',
          'referenced_columns' => 
          array (
            0 => 'id',
          ),
          'on_delete' => 'CASCADE',
        ),
        'fk_menu_items_parent' => 
        array (
          'columns' => 
          array (
            0 => 'parent_id',
          ),
          'referenced_table' => 'menu_items',
          'referenced_columns' => 
          array (
            0 => 'id',
          ),
          'on_delete' => 'CASCADE',
        ),
        'fk_menu_items_page' => 
        array (
          'columns' => 
          array (
            0 => 'page_id',
          ),
          'referenced_table' => 'pages',
          'referenced_columns' => 
          array (
            0 => 'id',
          ),
          'on_delete' => 'SET NULL',
        ),
      ),
    ),
  ),
);
