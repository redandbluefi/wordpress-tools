<?php
namespace rnb\media;

/**
 * Returns image element.
 * Usage: <?=\rnb\media\image($image, 'your-size')?>
 *
 * @param mixed $image
 * @param string $size
 * @param boolean $responsive
 */
function image($image, $size = 'medium', $responsive = true) {
  $data = get_image_data($image, $responsive);

  if (!$data) {
    return false;
  }

  // If the title contains the filename, don't use a title.
  $has_title = strpos($data[src], $data['title']) > -1 ? false : true;
  $class = $responsive ? 'wpt-image wpt-image--responsive' : 'wpt-image';

  return \rnb\core\tag([
    "<img src='$data[src]'",
    $responsive ? "srcset='$data[srcset]'" : "",
    $has_title ? "title='$data[title]'" : "",
    "class='$class'",
    "alt='$data[alt]'>"
  ]);
}

function get_image_data($image, $responsive = true) {
  if (is_array($image)) {
    $id = $image['ID'];
  } else if (is_numeric($image)) {
    $id = absint($image);
  } else {
    trigger_error('$image must be an array or id', E_USER_WARNING);
    return false;
  }

  $attachment = get_post($id);

  return [
    'src' => wp_get_attachment_image_url($id, $size),
    'srcset' => wp_get_attachment_image_srcset($id, $size),
    'alt' => get_post_meta($id, '_wp_attachment_image_alt', true),
    'caption' => $attachment->post_excerpt,
    'description' => $attachment->post_content,
    'title' => $attachment->post_title
  ];
}


/**
 * Returns URl for local resource, relative to theme root.
 * Usage: <?=\rnb\media\themeresource($image, 'your-size')?>
 *
 * @param string $resource
 */
function themeresource(string $resource = '') {
  $path = '';
  $localpath = '';

  if (function_exists('getenv')) {
    $errorlevel = getenv('WP_ENV') === 'development'
      ? E_USER_ERROR
      : E_USER_WARNING;
  } else {
    $errorlevel = E_USER_WARNING;
  }

  if (is_child_theme()) {
    // If it's a child theme, we're most likely going to want to get the
    // resources from the child theme dir.
    $path = get_stylesheet_directory_uri();
    $localpath = get_stylesheet_directory();
  } else {
    $path = get_template_directory_uri();
    $localpath = get_template_directory();
  }

  $path = $path . DIRECTORY_SEPARATOR;
  $localpath = $localpath . DIRECTORY_SEPARATOR;

  if (!file_exists($localpath . $resource)) {
    trigger_error("No file was found at {$localpath}{$resource}.", $errorlevel);
  }

  return $path . $resource;
}
