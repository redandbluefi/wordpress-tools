<?php
/**
 * wordpress-tools core
 */

namespace rnb\core;

/**
 * Registers Polylang strings. Must be called in admin.
 *
 */
function register_strings() {

  $strings = [
    'Recent string in breadcrumb' => 'recent',
    'Search string in breadcrumb' => 'search'
  ];

  if (function_exists('pll_register_string')) {
    foreach ($strings as $ctx => $string) {
      pll_register_string($ctx, $string, 'rnb_tools');
    }
  }
}

/**
 * Returns current env, defaulting to production if none set.
 *
 * @return string
 */
function env() {
  if (defined('WP_ENV')) {
    return WP_ENV;
  } else {
    define('WP_ENV', getenv('WP_ENV') ?? 'production');
  }

  return WP_ENV;
}

/**
 * Return wether env is production or not.
 *
 * @return boolean
 */
function is_prod() {
  return env() === 'production';
}

/**
 * Return wether env is development or not.
 *
 * @return boolean
 */
function is_dev() {
  return env() === 'development';
}

/**
 * Concats strings and arrays into one string. Useful for tags.
 *
 * @param mixe} $parts
 * @param string $glue
 *
 * @return string
 */
function tag($parts = [], $glue = "\n") {
  foreach ($parts as $key => $part) {
    // array map sucks in PHP
    if (!is_array($part)) {
      $parts[$key] = [$part];
    }
  }

  $html = "";
  $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($parts));
  foreach ($it as $key => $part) {
    $html .= $part . $glue;
  }

  return $html;
}

/**
 * Return the current, full URL.
 * Because PHP is incompetent and unable to do so with a single server var.
 *
 * @return string
 */
function current_url() {
  $protocol = (isset($_SERVER['HTTPS']) ? "https" : "http");
  return "$protocol://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

/**
 * Return string in slugish format. Useful for creating HTML ids and such.
 *
 * @param string $string
 *
 * @return string
 */
function slugify($string = '') {
  $string = str_replace(' ', '-', $string);
  $string = strtolower($string);
  return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
}

/**
 * Replacement for wp_enqueue_script & wp_enqueue_style. Handles cachebusting hashes.
 * define('WPT_ENQUEUE_STRIP_PATH', '/data/wordpress/htdocs');
 * \rnb\core\enqueue(get_stylesheet_directory() . '/build/client.*.js');
 *
 * @param string $path
 * @param array $deps
 *
 * @return void
 */
function enqueue($path = NULL, $deps = [], $external = false) {
  if (is_null($path)) {
    trigger_error('Enqueue path must not be empty', E_USER_ERROR);
  } else if (!defined('WPT_ENQUEUE_STRIP_PATH')) {
    trigger_error('You must define WPT_ENQUEUE_STRIP_PATH, 99% of the time it\'s /data/wordpress/htdocs', E_USER_ERROR);
  }

  if ($external) {
    $file = $path;
  } else {
    $files = glob($path, GLOB_MARK);
    $unhashed = str_replace("*.", "", $path);
    if (file_exists($unhashed)) {
      $files[] = $unhashed;
    }

    usort($files, function($a, $b) {
      return filemtime($b) - filemtime($a);
    });

    $file = $files[0];
  }

  $parts = explode(".", $file);
  $type = array_reverse($parts)[0];
  $handle = basename($parts[0]) . "-" . $type;

  $file = str_replace(WPT_ENQUEUE_STRIP_PATH, "", $file);

  switch($type) {
    case "js":
      \wp_enqueue_script($handle, $file, $deps, false, true);
    break;

    case "css":
      \wp_enqueue_style($handle, $file, $deps, false, 'all');
      break;

    default:
      \rnb\debug\dump($file, $parts, $type, $handle);
      trigger_error('Enqueued file must be a css or js file.', E_USER_ERROR);
  }
}

/**
 * Run wordpress-tools core operations.
 *
 */
function init() {
  register_strings();
}
