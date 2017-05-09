<?php
namespace rnb\post;

function get_excerpt($post_id = NULL, $fallback = true) {
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

function get_preview($post_id = NULL) {
  if(is_null($post_id)) {
    $post_id = get_the_ID();
  }

  $str = wpautop(get_post($post_id)->post_content);
  $str = substr($str, 0, strpos($str, '</p>') + 4);
  $str = strip_tags($str, '<a><strong><em>');

  return $str;
}

function excerpt($post_id = NULL, $fallback = true) {
  $excerpt = get_excerpt($post_id, $fallback);

  return \rnb\core\tag([
    "<div class='wpt-excerpt'>",
    strpos($excerpt, "<p>") > -1
      ? $excerpt
      : "<p>$excerpt</p>",
    "</div>"
  ]);
}

function preview($word_count = 30, $more = "&hellip;", $post_id = NULL) {
  $preview = get_preview($post_id);

  return \rnb\core\tag([
    "<div class='wpt-preview'>",
      "<p>" . wp_trim_words($preview, $word_count, $more) . "</p>",
    "</div>"
  ]);
}
