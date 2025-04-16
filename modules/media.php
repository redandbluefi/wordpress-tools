<?php
/**
 * Media related tools.
 */

namespace rnb\media;

/**
 * Returns an image element.
 * Usage: <?=\rnb\media\image($image, 'your-size')?>
 *
 * @param mixed $image
 * @param string $size
 * @param boolean $responsive
 */
function image($image, $size = 'medium', $responsive = true) {
  $data = get_image_data($image, $size);

  if (!$data) {
    return false;
  }

  // If the title contains the filename, don't use a title.
  $has_title = strpos($data['src'], $data['title']) > -1 ? false : true;
  $class = $responsive ? 'wpt-image wpt-image--responsive' : 'wpt-image';

  return \rnb\core\tag([
    "<img src='$data[src]'",
    $responsive ? "srcset='$data[srcset]'" : "",
    $has_title ? "title='$data[title]'" : "",
    "class='$class'",
    "alt='$data[alt]'>"
  ]);
}

/**
 * Returns an image element with captions.
 *
 * @param mixed $image
 * @param string $size
 * @param boolean $responsive
 */
function captioned_image($image, $size, $responsive = true) {
  $image = image($image, $size, $responsive);

  if (!$image) {
    return false;
  }

  $caption = get_image_data($image, $size)['caption'];

  return \rnb\core\tag([
    "<figure class='wpt-captioned'>",
      $image,
      "<figcaption class='wpt-captioned__caption'>",
        $caption,
      "</figcaption>",
    "</figure>"
  ]);
}

/**
 * Return all relevant data for an image as an array.
 *
 * @param mixed $image
 * @param string $size
 */
function get_image_data($image, $size = 'medium') {
  if (is_array($image)) {
    $id = $image['ID'];
  } else if (is_numeric($image)) {
    $id = absint($image);
  } else {
    throw new \Exception('$image must be an array or id');
    return false;
  }

  // Cache the call so we won't have to fetch the data again and again...

  $key = "wpt_gid_$id";
  if (!\rnb\core\is_dev()) {
    $transient = get_transient($key);
  } else {
    $transient = false;
  }

  if ($transient) {
    return $transient;
  } else {
    $attachment = get_post($id);
    $data = [
      'src' => wp_get_attachment_image_url($id, $size),
      'srcset' => wp_get_attachment_image_srcset($id, $size),
      'alt' => get_post_meta($id, '_wp_attachment_image_alt', true),
      'caption' => $attachment->post_excerpt,
      'description' => $attachment->post_content,
      'title' => $attachment->post_title
    ];

    set_transient(
      $key,
      $data,
      apply_filters(
        'rnb_tools_media_get_image_data_transient',
        MINUTE_IN_SECONDS
      )
    );

    return $data;
  }
}


/**
 * Returns URl for local resource, relative to theme root.
 * Usage: <?=\rnb\media\themeresource($image, 'your-size')?>
 *
 * @param string $resource
 * @param string $mode Determines wether to return server side or client side path.
 */
function themeresource(string $resource = '', $mode = 'uri') {
  $path = '';
  $localpath = '';

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
    return false;
  }

  if ($mode === 'local') {
    return $localpath . $resource;
  }

  return $path . $resource;
}

/**
 * Return an inline svg element, contained in a wrapper div.
 *
 * @param mixed $name
 * @param string $custom_class
 */
function inline_svg($name, string $custom_class = '') {
  $checklist = [$name, "$name.svg", "icon_$name.svg"]; // check in this order
  $custom_class = $custom_class ? ' ' . $custom_class : '';
  $build_svg = function($path) use ($custom_class) {
    $svg = file_get_contents($path);
    return "<div class='wpt-inline-svg" . $custom_class . "'>$svg</div>";
  };

  foreach ($checklist as $file) {
    $filepath = themeresource($file, 'local');
    if ($filepath) {
      return $build_svg($filepath);
    }

    return false;
  }

  return false;
}
