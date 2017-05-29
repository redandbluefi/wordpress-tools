<?php
/**
 * Single post related tools.
 */

namespace rnb\post;

/**
 * Return post excerpt. Tries to get a dedicated custom field first, then falls
 * back to native excerpt and finally gets a preview from content unless told not to.
 *
 * @param mixed $post_id
 * @param boolean $fallback
 */
function get_excerpt($post_id = null, $fallback = true) {
  if(is_null($post_id)) {
    $post_id = get_the_ID();
  }

  if($acf_ingress = get_field("ingress", $post_id)) {
    return $acf_ingress;
  } else if(has_excerpt($post_id)) {
    return get_the_excerpt($post_id);
  } else if ($fallback) {
    return get_preview($post_id);
  }

  return false;
}

/**
 * Get preview from the content. Stops at first paragraph.
 *
 * @param mixed $post_id
 */
function get_preview($post_id = null) {
  if(is_null($post_id)) {
    $post_id = get_the_ID();
  }

  $str = wpautop(get_post($post_id)->post_content);
  $str = substr($str, 0, strpos($str, '</p>') + 4);
  $str = strip_tags($str, '<a><strong><em>');

  return $str;
}

/**
 * Return the excerpt as a template tag, using get_excerpt().
 *
 * @param mixed $post_id
 * @param boolean $fallback
 */
function excerpt($post_id = null, $fallback = true) {
  $excerpt = get_excerpt($post_id, $fallback);

  return \rnb\core\tag([
    "<div class='wpt-excerpt'>",
    strpos($excerpt, "<p>") > -1
      ? $excerpt
      : "<p>$excerpt</p>",
    "</div>"
  ]);
}

/**
 * Return a preview of the post as a template tag, using get_preview().
 *
 * @param int $word_count
 * @param string $more
 * @param mixed $post_id
 */
function preview($word_count = 30, $more = "&hellip;", $post_id = null) {
  $preview = get_preview($post_id);

  return \rnb\core\tag([
    "<div class='wpt-preview'>",
      "<p>" . wp_trim_words($preview, $word_count, $more) . "</p>",
    "</div>"
  ]);
}
