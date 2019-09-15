<?php
/**
 * custom taxonomy types tools.
 */

use rnb\core;

namespace rnb\taxonomy_type;

/**
 * Create custom taxonomy to selected post types.
 * Usage: \rnb\taxonomy_type\create_taxonomy_type($name, $post_types, $args)
 *
 * @param string $name
 * @param array $post_types
 * @param array $args
 */
function create_taxonomy_type($name, $post_types, $args = []) {
  $taxonomy_type_slug = \rnb\core\slugify($name);
  $taxonomy_type_args = [
    'hierarchical' => true,
      'labels' => array(
        'name' => __($name),
        'singular_name' => __($name)
      ),
      'show_ui' => true,
      'publicly_queryable' => false,
      'show_in_rest' => true,
      'update_count_callback' => '_update_post_term_count',
      'query_var' => true,
      'rewrite' => array('slug' => $taxonomy_type_slug)
  ];
  if (!empty($args)) {
    $taxonomy_type_args = array_merge($taxonomy_type_args, $args);
  }

  register_taxonomy($taxonomy_type_slug, $post_types, $taxonomy_type_args);
}