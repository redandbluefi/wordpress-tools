# wordpress-tools
Bag of tools to lessen the pain of WordPress development. [Documentation](https://redandbluefi.github.io/wordpress-tools/) exists, and might improve over time.

It's modular, so you can disable the parts you don't need.

## Interesting parts
There's a lot going on, and some things aren't as useful as the other. It brings a new vibe to templating. 

First, we "register" a template directory, add this to your theme's functions.php
```
\rnb\template\load_glob(dirname(__FILE__) . '/templates/*');
```

Then every PHP file from `templates/` is loaded and usable by `\rnb\template\output()` and `\rnb\template\to_string()`. You could have multiple template directories if you wanted to. 

Templates differ a bit from "traditional" templates: all templates are functions that are given data as parameters, while the traditional template usually just calls a function directly. Example template, located in `templates/single-item.php`:
```php
function single_item($props = []) {
  // We can also let the template populate itself with data if required.
  $content = $props['content'] ?? get_the_content();
  $image = $props['image'] ?? \rnb\media\image(get_post_thumbnail_id());
  $injectorfn = !is_null($data['injectorfn']) ? $data['injectorfn'] : function() {
    return '<!-- You can put just about anything here -->';
  }; ?>
  
  <article>
    <?=$image?>
    <p><?=$content?></p>
    <?=$injectorfn()?> <!-- You could make the function echo data instead, choice is yours. -->
  </article>
}
```

You could then use that template in The Loop, or supply the data in any other way. 
```php
while (have_posts()) { the_post();
  \rnb\template\output('single_item');
}

// or

\rnb\template\output('single_item', [ // notice array inside array
  [
    'content' => 'Hello world!',
    'image' => '<img src="/path/to/image.jpg">',
    'injectorfn' => function() {
      $link = get_permalink();
      return \rnb\core\tag([
        "<a href='$link'>",
        "Read the whole thing",
        "</a>"
      ]);
    }
  ]
]);
```

This was all written inside the GitHub editor without actually testing the code, so if it works, great! If it doesn't, fix it and PR it please :) 
