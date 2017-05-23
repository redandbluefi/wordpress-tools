<?php
/**
 * Internationalization tools.
 */

namespace rnb\i18n;

/**
 * Return a field from ACF options page using the current language.
 *
 * You must construct your option pages like this:
 * Base: http://i.imgur.com/8ypMOjx.png
 * Actual options, do for every language: http://i.imgur.com/uwvsgMj.png
 *
 * @param string $option
 * @param string $location
 * @param boolean $format
 */
function get_acf_option($option = '', $location = 'options', $format = true) {
  $lang = pll_current_language();
  return get_field("{$lang}_{$option}", $location, $format);
}
