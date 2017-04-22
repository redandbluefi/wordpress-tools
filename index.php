<?php
/*
Plugin Name: WordPress Tools
Description: Tools.
Version: 0.1
Author: Christian Nikkanen / redandblue
Author URI: https://redandblue.fi
License: MIT
*/

$module_dir = dirname(__FILE__) . '/modules/';
$modules = apply_filters('rnb_tools_active_modules', [
  'core' => $module_dir . 'core.php'
]);

foreach ($modules as $name => $path) {
  require_once $path;
}
