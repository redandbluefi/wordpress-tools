<?php
/**
 * custom post types tools.
 */

namespace rnb\post_type;

/**
 * Create custom post types.
 * Usage: \rnb\post_type\create_post_type($name, $args)
 *
 * @param string $name
 * @param array $args
 */
function create_post_type($name, $args = []) {
  $post_type_slug = \rnb\core\slugify($name);
  $post_type_args = [
    'labels' => [
      'name' => pll__($name),
      'singular_name' => pll__($name),
    ],
    'public' => true,
    'rewrite' => [
      'slug' => \rnb\core\slugify($name),
      'with_front' => false,
    ],
    'has_archive' => true,
    'exclude_from_search' => false,
    'publicly_queryable' => null,
    'show_ui' => true,
    'show_in_nav_menus' => true,
    'show_in_rest' => true,
    'hierarchical' => false,
    'supports' => [
      'title',
      'editor',
      'revisions',
      'author',
      'excerpt',
      'page-attributes',
      'thumbnail',
    ],
    'capability_type' => 'post',
  ];
  if (!empty($args)) {
    $post_type_args = array_merge($post_type_args, $args);
  }

  register_post_type($post_type_slug, $post_type_args);
}