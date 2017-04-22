<?php
namespace rnb\template;

/**
 * Retrieves highest priority template. Searches STYLESHEETPATH before
 * TEMPLATEPATH, which means it will prefer child theme templates over parent.
 *
 * @param string $template Relative path from theme root, usually
 * templates/file.php
 * @param array $variables Anything you pass with this array will be used as
 * function parameters for the template.
 *
 * @return boolean
 */
function get(string $template = '', array $variables = []) {
  if (!$template) {
    throw new Exception('Template cannot be empty!');
  }

  foreach ($variables as $key => $value) {
    ${$key} = $value;
  }

  $template = locate_template($template);

  if (!empty($template)) {
    require($template);
    return true;
  }

  return false;
}

/**
 * Return list of files in path relative to current file.
 *
 * @param string $path
 */
function list ($path = '.') {
  $path = rtrim($path, '/');
  return glob(__DIR__ . "/$path/*");
}
