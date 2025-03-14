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
function enqueue($path = null, $deps = [], $external = false) {

  $is_bedrock = file_exists(dirname(__DIR__, 4) . '/wp/wp-load.php');

  if (is_null($path)) {
    trigger_error('Enqueue path must not be empty', E_USER_ERROR);
  } else if (!defined('WPT_ENQUEUE_STRIP_PATH')) {
    trigger_error('You must define WPT_ENQUEUE_STRIP_PATH, 99% of the time it\'s /data/wordpress/htdocs', E_USER_ERROR);
  }

  if ($external) {
    $file = $path;
    $filetime = false;
  } else {
    $filetime = filemtime($path);
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

  $file = $is_bedrock ? str_replace( dirname( rtrim(ABSPATH,"/") )."/app","",$file) : str_replace(WPT_ENQUEUE_STRIP_PATH, "", $file);

  if($is_bedrock) {
    $file = get_stylesheet_directory_uri() . str_replace("/themes/pro-artibus-theme", "", $file);
  }

  // Some externals won't have filetype in the URL, manual override.
  if (strpos($path, "fonts.googleapis") > -1) {
    $type = "css";
    $handle = "fonts";
  } else if (strpos($path, "polyfill.io") > -1) {
    $type = "js";
    $handle = "polyfill";
  } else if (strpos($path, "maps.googleapis") > -1) {
    $type = "js";
    $handle = "maps";
  }

  switch($type) {
    case "js":
      \wp_enqueue_script($handle, $file, $deps, $filetime, true);
    break;

    case "css":
      \wp_enqueue_style($handle, $file, $deps, $filetime, 'all');
      break;

    default:
      throw new \Exception('Enqueued file must be a css or js file.');
  }
}

/**
 * Run wordpress-tools core operations.
 *
 */
function init() {
  register_strings();
}
