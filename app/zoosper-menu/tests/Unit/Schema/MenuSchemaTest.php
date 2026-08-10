<?php
declare(strict_types=1);
it('owns menu and menu item schema with site and nesting indexes',function(){ $schema=require dirname(__DIR__,3).'/config/db_schema.php';expect($schema['tables'])->toHaveKeys(['menus','menu_items'])->and($schema['tables']['menus']['indexes'])->toHaveKey('uniq_menus_site_code')->and($schema['tables']['menu_items']['indexes'])->toHaveKey('idx_menu_items_menu_parent_position');});
