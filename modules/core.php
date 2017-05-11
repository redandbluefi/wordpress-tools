<?php
namespace rnb\core;

/**
 * This must be called in admin, it does nothing when called in client.
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

function env() {
  if (defined('WP_ENV')) {
    return WP_ENV;
  } else {
    define('WP_ENV', getenv('WP_ENV') ?? 'production');
  }

  return WP_ENV;
}

function is_prod() {
  return env() === 'production';
}

function is_dev() {
  return env() === 'development';
}

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
  // \rnb\debug\dump($html);

  return $html;
  // \rnb\debug\dump($parts);
  // return \join($glue, $parts);
}

function init() {
  register_strings();
}

