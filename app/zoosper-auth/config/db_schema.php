<?php

declare(strict_types=1);

return array (
  'tables' => 
  array (
    'admin_users' => 
    array (
      'columns' => 
      array (
        'locale' => 
        array (
          'type' => 'string',
          'length' => 16,
          'nullable' => true,
        ),
        'id' => 
        array (
          'type' => 'integer',
          'primary' => true,
          'auto_increment' => true,
        ),
      ),
    ),
    'admin_roles' => 
    array (
      'columns' => 
      array (
        'id' => 
        array (
          'type' => 'integer',
          'primary' => true,
          'auto_increment' => true,
        ),
      ),
    ),
    'admin_permissions' => 
    array (
      'columns' => 
      array (
        'id' => 
        array (
          'type' => 'integer',
          'primary' => true,
          'auto_increment' => true,
        ),
      ),
    ),
    'admin_user_roles' => 
    array (
      'columns' => 
      array (
        'user_id' => 
        array (
          'type' => 'integer',
          'nullable' => false,
        ),
        'role_id' => 
        array (
          'type' => 'integer',
          'nullable' => false,
        ),
      ),
      'indexes' => 
      array (
        'uniq_admin_user_roles' => 
        array (
          'columns' => 
          array (
            0 => 'user_id',
            1 => 'role_id',
          ),
          'unique' => true,
        ),
      ),
      'foreign_keys' => 
      array (
        'fk_admin_user_roles_user' => 
        array (
          'columns' => 
          array (
            0 => 'user_id',
          ),
          'referenced_table' => 'admin_users',
          'referenced_columns' => 
          array (
            0 => 'id',
          ),
          'on_delete' => 'CASCADE',
        ),
        'fk_admin_user_roles_role' => 
        array (
          'columns' => 
          array (
            0 => 'role_id',
          ),
          'referenced_table' => 'admin_roles',
          'referenced_columns' => 
          array (
            0 => 'id',
          ),
          'on_delete' => 'CASCADE',
        ),
      ),
    ),
    'admin_role_permissions' => 
    array (
      'columns' => 
      array (
        'role_id' => 
        array (
          'type' => 'integer',
          'nullable' => false,
        ),
        'permission_id' => 
        array (
          'type' => 'integer',
          'nullable' => false,
        ),
      ),
      'indexes' => 
      array (
        'uniq_admin_role_permissions' => 
        array (
          'columns' => 
          array (
            0 => 'role_id',
            1 => 'permission_id',
          ),
          'unique' => true,
        ),
      ),
      'foreign_keys' => 
      array (
        'fk_admin_role_permissions_role' => 
        array (
          'columns' => 
          array (
            0 => 'role_id',
          ),
          'referenced_table' => 'admin_roles',
          'referenced_columns' => 
          array (
            0 => 'id',
          ),
          'on_delete' => 'CASCADE',
        ),
        'fk_admin_role_permissions_permission' => 
        array (
          'columns' => 
          array (
            0 => 'permission_id',
          ),
          'referenced_table' => 'admin_permissions',
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
