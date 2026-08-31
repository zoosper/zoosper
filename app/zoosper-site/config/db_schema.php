<?php

declare(strict_types=1);

return array (
  'tables' => 
  array (
    'sites' => 
    array (
      'columns' => 
      array (
        'theme_code' => 
        array (
          'type' => 'string',
          'length' => 120,
          'nullable' => false,
          'default' => 'default',
        ),
        'locale' => 
        array (
          'type' => 'string',
          'length' => 16,
          'nullable' => false,
          'default' => 'en_AU',
        ),
        'currency' => 
        array (
          'type' => 'string',
          'length' => 8,
          'nullable' => false,
          'default' => 'AUD',
        ),
        'base_url' => 
        array (
          'type' => 'string',
          'length' => 255,
          'nullable' => false,
          'default' => '',
        ),
        'website_code' => 
        array (
          'type' => 'string',
          'length' => 64,
          'nullable' => false,
          'default' => 'main',
        ),
        'store_code' => 
        array (
          'type' => 'string',
          'length' => 64,
          'nullable' => false,
          'default' => 'main',
        ),
        'store_view_code' => 
        array (
          'type' => 'string',
          'length' => 64,
          'nullable' => false,
          'default' => 'default',
        ),
        'path_prefix' => 
        array (
          'type' => 'string',
          'length' => 190,
          'nullable' => false,
          'default' => '',
        ),
        'id' => 
        array (
          'type' => 'integer',
          'primary' => true,
          'auto_increment' => true,
        ),
      ),
      'indexes' => 
      array (
        'idx_sites_theme_code' => 
        array (
          'columns' => 
          array (
            0 => 'theme_code',
          ),
        ),
        'idx_sites_store_view_code' => 
        array (
          'columns' => 
          array (
            0 => 'store_view_code',
          ),
        ),
      ),
    ),
    'site_domains' => 
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
        'host' => 
        array (
          'type' => 'string',
          'length' => 190,
          'nullable' => false,
        ),
        'is_primary' => 
        array (
          'type' => 'integer',
          'nullable' => false,
          'default' => 0,
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
        'idx_site_domains_site_id' => 
        array (
          'columns' => 
          array (
            0 => 'site_id',
          ),
        ),
        'uniq_site_domains_host' => 
        array (
          'columns' => 
          array (
            0 => 'host',
          ),
          'unique' => true,
        ),
      ),
      'foreign_keys' => 
      array (
        'fk_site_domains_site' => 
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
  ),
);










