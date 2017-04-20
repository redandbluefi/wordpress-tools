# wordpress-tools
Rewrite of wp-php-helpers

plan: namespace rnb
  - namespace post, helpers for single posts
  - namespace posts, helpers for post
  - namespace taxonomy, helpers for taxonomies
  - namespace media, helpers for media related things 
  - namespace template, helpers for template tags and so on
  
  
 Syntax: 
 ```
 <?php
 // Think templates as functions!
 \rnb\template\get('templates/template.php', [
   'variable' => 'anything',
   'query' => new WP_Query()
 ]);
 

 <?php
 // template.php
 // PHP 7. If $query exists (remember, this is a function!), use it. If not, do something else.
 $query = $query ?? new WP_Query([ 
   'default' => 'args'
 ]); 
 // "Get used to it" - ReactJS 2013
 // May feel hacky, but globals are just wrong. 
  ```
