<?php
/**
 * Debug related tools.
 */

namespace rnb\debug;

/**
 * Takes all parameters and dumps them in a nice readable format. Unlike with xdebug var_dump or print_r,
 * flexbox shouldn't ruin the day.
 *
 */
function dump() {
  $args = func_get_args();
  $styles = "padding: .5rem 1rem; background: #fbfbfb; overflow-x: scroll;";
  $styles .= "max-width: 100%;";
  echo "<pre style='$styles'>";
  foreach ($args as $i => $arg) {
    print_r($arg);

    if ($i + 1 !== count($args)) {
      echo "\n<hr>\n";
    }
  }
  echo "</pre>";
}
