<?php
/**
 * Contains the breadcrumb.
 */

namespace rnb\breadcrumb;

class Breadcrumb {
  public function __construct($separator, $home_text) {
    $this->separator = apply_filters('rnb_tools_breadcrumb_separator', $separator);
    $this->home_text = $home_text;

    /*
     * dirty hack to get around warning in search page. This is only needed
     * for the transient key.
     */
    global $pagenow;
    $this->queried = get_queried_object();
    $this->queried = !empty($this->queried)
      ? $this->queried
      : (object) array("name" => 'search');

    $salt = is_singular() ? get_the_ID() : (is_post_type_archive() ? get_the_archive_title() : $this->queried->name);
    $this->transient_key = md5($pagenow . $salt);
  }

  public function create() {
    $queried = $this->queried;
    $open = "<div class='rnb-breadcrumb'>";
    $close = "</div>";

    $home_url = apply_filters('rnb_tools_home_url', get_home_url());
    $home = "<a href='$home_url' class='home-url'>$this->home_text</a>";
    $items = "";

    if (class_exists('PLL')) {
      $recent = pll__('recent');
      $search = pll__('search');
    } else {
      $locale = get_locale();

      switch($locale) {
        case "fi":
          $recent = 'Ajankohtaista';
          $search = 'Haku';
        break;

        case "en":
          $recent = 'Recent';
          $search = 'Search';
        break;

        case "sv":
          $recent = 'Senaste';
          $search = 'Sök';
        break;

        default:
          $recent = 'Recent';
          $search = 'Search';
        break;
      }
    }

    $recent = apply_filters('rnb_tools_breadcrumb_recent_text', $recent);
    if (is_category()){
      $link = get_category_link($queried->term_id);
      $items .= apply_filters('rnb_tools_breadcrumb_category', "$this->separator <a href='$link'>{$queried->name}</a> ");
    } else if (is_archive()) {
      $link = get_post_type_archive_link($queried->name);
      $items .= apply_filters('rnb_tools_breadcrumb_archive', "$this->separator <a href='$link'>{$queried->label}</a> ");
    } elseif (get_the_ID() === get_option('page_for_posts')){
      $link = get_post_type_archive_link($queried->name);
      $items .= apply_filters('rnb_tools_breadcrumb_home', "$this->separator <a href='$link'>$recent</a> ");
    } elseif (is_home()) {
      // These two last are basically the same thing. But this is for corner cases.
      $link = get_post_type_archive_link($queried->name);
      $items .= apply_filters('rnb_tools_breadcrumb_home', "$this->separator <a href='$link'>$recent</a> ");
    }

    if (is_search()) {
      $link = '?s=';
      $items .= apply_filters('rnb_tools_breadcrumb_search', "$this->separator <a href='$link'>$search</a> ");
    }

    if (is_singular()) {
      $ancestors = get_post_ancestors(get_the_ID());
      $post_type_obj = get_post_type_object($queried->post_type);
      $has_archive = $post_type_obj->has_archive;

      if ($has_archive){
        $link = get_post_type_archive_link($queried->post_type);
        $items .= apply_filters(
          'rnb_tools_breadcrumb_single_cpt_archive',
          "$this->separator <a href='$link'>{$post_type_obj->label}</a> "
        );

      } elseif($queried->post_type === "post") {
        $link = get_post_type_archive_link($queried->post_type);
        $items .= apply_filters(
          'rnb_tools_breadcrumb_single_post_archive',
          "$this->separator <a href='$link'>{$post_type_obj->label}</a> "
        );
      }

      if (!empty($ancestors) && apply_filters('rnb_tools_breadcrumb_enable_ancestors', true)) {
        $ancestors = array_reverse($ancestors);
        foreach($ancestors as $ancestor) {
          $link = get_permalink($ancestor);
          $title = get_the_title($ancestor);

          $items .= "$this->separator <a href='$link'>$title</a> ";
        }
      }

      $link = get_permalink(get_the_ID());
      $title = get_the_title(get_the_ID());

      $items .= apply_filters('rnb_tools_breadcrumb_current', "$this->separator <a href='$link'>$title</a> ");

    }

    $open = apply_filters('rnb_tools_breadcrumb_open', $open);
    $close = apply_filters('rnb_tools_breadcrumb_close', $close);

    $breadcrumb = apply_filters('rnb_tools_breadcrumb', "$open $home $items $close");
    set_transient($this->transient_key, $breadcrumb, apply_filters('rnb_tools_breadcrumb_transient', 60 * 5));

    return $breadcrumb;
  }

  public function get_cached() {
    return get_transient($this->transient_key);
  }

  public function print() {
    if (\rnb\core\is_prod() && apply_filters('rnb_tools_breadcrumb_use_cache', true)) {
      $cached = $this->get_cached();
    } else {
      $cached = false;
    }

    if (!$cached) {
      $breadcrumb = $this->create();
    } else {
      $breadcrumb = $cached;
    }

    return $breadcrumb;
  }
}

function init($separator = '&rsaquo;', $home_text = '<i class="fa fa-home"></i>') {
  $instance = new Breadcrumb($separator, $home_text);
  return $instance->print();
}
