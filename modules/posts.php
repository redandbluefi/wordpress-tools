<?php
/**
 * Tools related to posts in general.
 */

namespace rnb\posts;

/**
 * Returns post type archive link, in a consistent way.
 *
 * @param string $post_type
 */
function archive_link($post_type = 'post') {
  if ($post_type === 'post') {
    return get_permalink(get_option('page_for_posts'));
  }

  $archive = get_post_type_archive_link($post_type);

  if (!$archive) {
    throw new \Exception("Post type has no archive or it doesn't exist.");
  }

  return $archive;
}
