<?php

function rnb_get_files_from_dir ($dir_name) {
  $results = [];

  $files_extensions = array(
    'php',
    'inc',
    'twig',
  );

  $files = scandir($dir_name);
  foreach ($files as $key => $value) {
    $path = realpath($dir_name . DIRECTORY_SEPARATOR . $value);
    if (!is_dir($path)) {
      $path_parts = pathinfo($path);
      if (!empty($path_parts['extension']) && in_array($path_parts['extension'], $files_extensions)) {
        $results[] = $path;
      }
    } else if ($value != "." && $value != "..") {
      $temp = rnb_get_files_from_dir($path);
      $results = array_merge($results, $temp);
    }
  }
  return $results;
}

function rnb_get_strings_from_files($files) {
  $singleQuoteTempPattern = "\'";
  $singleQuoteTempReg = '[\single_quote]';

  $doubleQuoteTempPattern = '\"';
  $doubleQuoteTempReg = '[\double_quote]';

  $strings = [];
  foreach ($files as $file) {
    $content = file_get_contents($file);
    $content = str_replace($singleQuoteTempPattern, $singleQuoteTempReg, $content);
    $content = str_replace($doubleQuoteTempPattern, $doubleQuoteTempReg, $content);
    // find polylang functions
    preg_match_all("/[\s=\(]+pll_[_e][\s]*\([\s]*[\'\"](.*?)[\'\"][\s]*\)/s", $content, $matches);
    if (!empty($matches[1])) {
      $strings = array_merge($strings, $matches[1]);
    }

    // find wp functions: __(), _e(), _x()
    preg_match_all("/[\s=\(\.]+_[_ex][\s]*\([\s]*[\'](.*?)[\'][\s]*[,]*[\s]*[\']*(.*?)[\']*[\s]*\)/s", $content, $matches);
    if (!empty($matches[1])) {
      $strings = array_merge($strings, $matches[1]);
    }
    preg_match_all("/[\s=\(\.]+_[_ex][\s]*\([\s]*[\"](.*?)[\"][\s]*[,]*[\s]*[\"]*(.*?)[\"]*[\s]*\)/s", $content, $matches);
    if (!empty($matches[1])) {
      $strings = array_merge($strings, $matches[1]);
    }

    // find wp functions: esc_html_e, esc_html, esc_html__, esc_attr, esc_attr_e, esc_attr__
    preg_match_all("/[\s=\(\.]+(esc_html|esc_attr)[_e]*[\s]*\([\s]*[\'](.*?)[\'][\s]*[,]*[\s]*[\']*(.*?)[\']*[\s]*\)/s", $content, $matches);
    if (!empty($matches[2])) {
      $strings = array_merge($strings, $matches[2]);
    }
    preg_match_all("/[\s=\(\.]+(esc_html|esc_attr)[_e]*[\s]*\([\s]*[\"](.*?)[\"][\s]*[,]*[\s]*[\"]*(.*?)[\"]*[\s]*\)/s", $content, $matches);
    if (!empty($matches[2])) {
      $strings = array_merge($strings, $matches[2]);
    }

    // find wp functions: _n: single + plural
    preg_match_all("/[\s=\(\.]+_n[\s]*\([\s]*[\'\"](.*?)[\'\"][\s]*,[\s]*[\'\"](.*?)[\'\"][\s]*,(.*?)\)/s", $content, $matches);
    if (!empty($matches[1])) {
      $strings = array_merge($strings, $matches[1]);
      $strings = array_merge($strings, $matches[2]);
    }
  }

  foreach ($strings as $key => $value) {
    // inverse quotes:
    $value = str_replace($singleQuoteTempReg, '\'', $value);
    $value = str_replace($doubleQuoteTempReg, "\"", $value);

    $strings[$key] = $value;
  }
  return $strings;
}

$rnb_set_strings_to_translate = function( $args = [], $assoc_args = [] ) {
  $start = microtime(true);
  $files = rnb_get_files_from_dir(get_template_directory());
  foreach(apply_filters( 'rnb_plugins_to_string_translation', [] ) as $plugin) {
    $files = array_merge($files, rnb_get_files_from_dir(WP_PLUGIN_DIR . '/' . $plugin));
  }
  $strings = rnb_get_strings_from_files($files);

  update_option('rnb_strings_to_translate', $strings, false);
  
  $execution_time =  microtime(true) - $start;
  WP_CLI::success( "Loop " .count($files). " files and added, " . count($strings) . " strings to translations. Execution time: " . $execution_time );
};

$rnb_set_parents = function( $args = [], $assoc_args = [] ) {
  $arguments = wp_parse_args( $assoc_args, array(
    'post-type'   => 'post',
    'parent'    => null
  ) );

  if(is_null($arguments['parent']) && is_numeric(intval($arguments['parent']))) {
    WP_CLI::error( " invalid parent ");
  }
  else {
    $count = setParentsOnPostType($arguments['post-type'], $arguments['parent']);
    
    WP_CLI::success( "$count ".$arguments['post-type']." -posts set to child of ".$arguments['parent'] );
  }

};

function setParentsOnPostType($post_type, $parrent_id = 0) {
  $counter = 0;
  $ok = true;

  $args = [
    'post_type'     => $post_type,
    'posts_per_page' => -1,
  ];
  $query = new WP_Query($args);
  
  if($query->have_posts()) { // if we have posts, then we update data
    
    while($query->have_posts()) {
      $query->the_post();
      wp_update_post([
        'ID' => get_the_ID(), 
        'post_parent' => $parrent_id
      ]);

      $counter++;
    }
    wp_reset_postdata();
  }
  return $counter;
}

$rnb_add_pages = function( $args = [], $assoc_args = [] ) {
  $arguments = wp_parse_args( $assoc_args, array(
    'menu'              => 'Main menu',
    'setup-nav'         => false,
    'set-primary-nav'   => true
  ) );

  $json = false;
  if(isset($args[0])) {
    $json = $args[0];
  }

  if(is_file(WP_CLI_PHAR_PATH."/".$json)) {
    $menu_id = 0;

    if($arguments['setup-nav']) {
      $menu_exists = wp_get_nav_menu_object( $arguments['menu'] );
      if ( ! $menu_exists ) {
        $menu_id = wp_create_nav_menu( $arguments['menu'] );
        if($arguments['set-primary-nav']) {
          $locations = get_theme_mod('nav_menu_locations');
          $locations['primary'] = $menu_id;
          set_theme_mod( 'nav_menu_locations', $locations );
        }
      }
      else {
        $menu_id = $menu_exists->term_id ;
      }
    }

    $postsTree = json_decode( file_get_contents(WP_CLI_PHAR_PATH."/".$json) );
    $counter = 0;
    foreach($postsTree->posts as $post) {
      $counter += rnb_add_page($post, $arguments['setup-nav'], $menu_id);
    }
    if($counter === 0) {
      WP_CLI::error("$counter pages added.");
    }
    else {
      WP_CLI::success( "Successfully added $counter pages." );
    }
  }
  else {
    WP_CLI::error("invalid file");
  }
};

function rnb_add_page($post, $setupNav = false, $menu_id = 0, $parent = 0, $parentMenuId = 0) {
  $ok = true;
  $counter = 0;

  $postArgs = [
    'post_title'      => trim($post->title),
    'post_type'       => "page",
    'post_status'     => 'publish',
    'post_name'       => sanitize_title($post->title)
  ];
  
  if($parent !== 0) {
    $postArgs["post_parent"] = $parent;
  }
  
  $pid = wp_insert_post($postArgs);
  
  
  if($pid === 0) {
    $ok = false;
  }
  
  if($ok) {
    $counter++;
    
    $navArgs = [
      'menu-item-title' => trim($post->title),
      'menu-item-object-id' => $pid,
      'menu-item-object' => 'page',
      'menu-item-status' => 'publish',
      'menu-item-type' => 'post_type',
    ];

    if($parentMenuId !== 0) {
      $navArgs["menu-item-parent-id"] = $parentMenuId;
    }
    
    if($setupNav && $menu_id !== 0) {
      $parentMenuId = wp_update_nav_menu_item($menu_id, 0, $navArgs);
    }

    if(isset($post->posts)) {
      foreach($post->posts as $child) {
        $counter += rnb_add_page($child, $setupNav, $menu_id, $pid, $parentMenuId);
      }
    }
  }

  return $counter;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
  /**
   * Set parent any parent for all post type posts
   * 
   * attributes:
   * - post-type = wp post type slug
   * - parent = wp post id
   * 
   * call `wp rnb set parents --post-type=type --parent=1`
   */
  WP_CLI::add_command( 'rnb set parents', $rnb_set_parents );

  /**
   * Set empty pages, hierarcy and menu from json.
   * 
   * attributes:
   * - menu = menu name (default Main menu)
   * - setup-nav = true / false (default false)
   * - set-primary-nav = true / false (default true)
   * 
   * call `wp rnb add pages file_name.json --setup-nav=true --menu="New menu"`
   * First argument must be file name, call this on folder where file is.
   * 
   * Json-file format:
   * {
	 * "posts": [
	 * 	{
	 *		"title": "Etusivu"
	 *	},
	 *	{
	 *		"title": "Palvelut",
	 *		"posts": [
	 *			{
	 *				"title": "Palvelu"
   *			},
   *    ]
   *   }
   *  }
   */
  WP_CLI::add_command( 'rnb add pages', $rnb_add_pages );

  /**
   * This command loops current theme and plugins that are set in 'rnb_plugins_to_string_translation' filter (array),
   * finds polylang translations and add them to options.
   * In theme we can get strings from that option and set string to be translated.
   * 
   * call `wp rnb set strings to translate`
   * 
   * We can use that wp-cli command on deploy action so this command run every time we deploy something.
   * 
   */
  WP_CLI::add_command( 'rnb set strings to translate', $rnb_set_strings_to_translate );
}
