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
function list_all ($path = '.') {
  $path = rtrim($path, '/');
  return glob(__DIR__ . "/$path/*");
}

/**
 * Returns a <time> element containing the time. Pass falsy value to format to
 * exclude them from output.
 *
 * @param string $date_format
 * @param string $time_format
 * @param mixed $time Optionally pass custom time
 */
function date($date_format = 'd.m.Y', $time_format = 'H:i', $time = NULL) {
  if (is_null($time)) {
    $date = get_the_date("$date_format | $time_format");
  } else {
    $date = \date("$date_format | $time_format", strtotime($time));
  }

  $datearray = explode(' | ', $date);

  return \rnb\core\tag([
    "<time class='wpt-time'>",
      $date_format ? "<span class='wpt-time__date'>$datearray[0]</span>" : '',
      $time_format ? "<span class='wpt-time__time'>$datearray[1]</span>" : '',
    "</time>"
  ]);
}
