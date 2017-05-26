<?php
/**
 * Template related tools.
 */

namespace rnb\template;

/**
 * Output renders a template. Templates are just functions that you can give parameters.
 * Be mindful that you could do this if you're stupid: 
 * output($_GET['template'], [$someparam]) => output('eval', ['get rekt'])
 * See sample_template() and sample_template_named_params().
 *
 * @param string $template
 * @param array $variables (optional)
 */
function output(string $template = '', array $variables = []) {
  if ($template === '' || !function_exists($template)) {
    throw new \Exception('Invalid template.');
  }

  return $template(...$variables);
}

/**
 * Returns template output as string, and discards any return value.
 * @param string $template
 * @param array $variables
 */
function to_string(string $template, array $variables = []) {
  ob_start();
  output($template, $variables);
  return ob_get_clean();
}

/**
 * Load templates from dir using glob.
 * Usage: load_glob(dirname(__FILE__) . '/templates/*')
 *
 * @param string $directory
 */
function load_glob($directory = './*') {
  foreach (glob($directory) as $filename) {
    require_once($filename);
  }
}

/**
 * Sample template for those unsure. This is the template init function, you
 * could use classes if you wanted. Usage:
 * <?=\rnb\template\get('\rnb\template\sample_template', ['Hello', 'world!']);
 *
 * @param string $title
 * @param string $content
 */
function sample_template($title = 'Hello', $content = 'you') { ?>
  <div>
    <h1><?=$title?></h1>
    <p><?=$content?></p>
  </div><?php
}

/**
 * Sample template using "named parameters". Usage:
 * <?=\rnb\template\get('\rnb\template\sample_template_named_params', [['title' => 'Hello world!']]);
 *
 * @param array $data
 */

function sample_template_named_params($data = []) {
  $title = $data['title'] ?? 'Default title';
  $content = $data['content'] ?? 'Default content';
  $template_hook = $data['template_hook'] ?? '<!-- You could inject stuff here -->';
  $template_fn = !is_null($data['template_fn']) ? $data['template_fn'] : function() { return 'Dummy function'; };
  ?>
  <div>
    <h1><?=$title?></h1>
    <p><?=$content?></p>
    <?=$template_hook?>
    <?=$template_fn()?>
  </div><?php
}


/**
 * Return list of files in path relative to current file.
 *
 * @param string $path
 */
function list_all($path = '.') {
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

/**
 * Returns a <a> element containing the "primary" (first) term. Optionally pass
 * and ID or term object to retrieve that term as an element.
 *
 * @param string $taxonomy
 * @param mixed $term
 * @param array $options (optional)
 */
function term($taxonomy = 'category', $term = NULL, $options = []) {
  $options['link_append'] = $options['link_append'] ?? ''; // append anchors or similar
  $options['link_class'] = $options['link_class'] ?? '';

  if (is_null($term)) {
    $data = \rnb\taxonomy\get_primary_term($taxonomy);
  } else {
    $data = \get_term($term, $taxonomy);
  }

  if (!$data) {
    trigger_error("No term found in $taxonomy.", E_USER_WARNING);
    return false;
  }

  $link = get_term_link($data, $taxonomy);

  return \rnb\core\tag([
    "<a ",
      "class='wpt-term $options[link_class]'",
      "href='{$link}{$options['link_append']}'",
      "data-slug='$data->slug' data-id='$data->ID'",
    ">",
      "<span class='wpt-term__name'>$data->name</span>",
    "</a>"
  ]);
}


/**
 * Returns a <a> element containing the permalink.
 *
 * @param mixed $post_id
 * @param string $text
 */
function readmore($post_id = NULL, $text = 'Read more') {
  if (is_null($post_id)) {
    $post_id = get_the_ID();
  }

  $text = apply_filters('rnb_tools_template_readmore', $text);
  $link = get_permalink($post_id);

  return \rnb\core\tag([
    "<a class='wpt-readmore' href='$link' data-id='$post_id'>",
      "<span class='wpt-readmore__text'>$text</span>",
    "</a>"
  ]);
}

/**
 * Prints a template. Templates can be just about anything, but they must have a
 * function with the name you use to print the template. See sample_template() and sample_template_named_params().
 *
 * @param string $template Template init function name
 * @param array $variables Array without string keys, will be passed as function arguments.
 *
 * @deprecated
 * @return mixed
 */
function get(string $template = '', array $variables = []) {
  if (!$template) {
    throw new \Exception('Template cannot be empty!');
  }

  if (function_exists($template)) {
    // return call_user_func_array($template, $variables);
    return $template(...$variables); // Argument unpacking is much faster!
  } else {
    // If you pass a filepath instead of a valid function name, that will be
    // used as template.
    foreach ($variables as $key => $value) {
      ${$key} = $value;
    }

    // Search possible child theme first.
    $template = locate_template($template);

    if (!empty($template)) {
      require($template);
      return true;
    }

    return false;
  }
}

/**
 * Return template instead of printing it. Uses get() internally.
 * @param string $template Function name
 * @param array $variables Anything you pass with this array will be used as
 * function parameters for the template.
 *
 * @deprecated
 *
 */
function save(string $template = '', array $variables = []) {
  ob_start();
  get($template, $variables);

  return ob_get_clean();
}

