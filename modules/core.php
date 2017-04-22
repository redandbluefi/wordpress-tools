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

function init() {
  register_strings();
}
