<?php
/*
Plugin Name: WordPress Tools
Description: Tools.
Version: 0.1
Author: Christian Nikkanen / redandblue
Author URI: https://redandblue.fi
License: MIT
*/

namespace rnb;

$module_dir = dirname(__FILE__) . '/modules/';
$modules = apply_filters('rnb_tools_active_modules', [
  'core' => $module_dir . 'core.php',
  'debug' => $module_dir . 'debug.php',
  'template' => $module_dir . 'template.php',
  'media' => $module_dir . 'media.php',
  'post' => $module_dir . 'post.php',
  'posts' => $module_dir . 'posts.php',
  'taxonomy' => $module_dir . 'taxonomy.php',
  'i18n' => $module_dir . 'i18n.php',
  'breadcrumb' =>$module_dir . 'breadcrumb.php',
  'cli' =>$module_dir . 'cli.php'
]);

foreach ($modules as $name => $path) {
  require_once $path;
}