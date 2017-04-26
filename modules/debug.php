<?php
namespace rnb\debug;

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
