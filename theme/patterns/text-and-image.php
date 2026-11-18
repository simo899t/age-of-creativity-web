<?php
/**
 * Title: Text next to an image
 * Slug: aoc/text-and-image
 * Categories: aoc
 * Keywords: image, picture, media, text
 * Description: A picture on one side and a heading with text on the other. Click the picture to replace it.
 */
?>
<!-- wp:media-text {"align":"wide","mediaType":"image","mediaWidth":45,"verticalAlignment":"center","className":"aoc-media-text"} -->
<div class="wp-block-media-text alignwide is-stacked-on-mobile is-vertically-aligned-center aoc-media-text" style="grid-template-columns:45% auto"><figure class="wp-block-media-text__media"><img src="<?php echo esc_url(get_theme_file_uri('assets/img/portrait-placeholder.svg')); ?>" alt=""/></figure><div class="wp-block-media-text__content">
  <!-- wp:heading {"level":2} -->
  <h2 class="wp-block-heading">A heading for this section</h2>
  <!-- /wp:heading -->
  <!-- wp:paragraph -->
  <p>Write a paragraph or two here. Pictures from the Media Library or your computer can replace the one on the left.</p>
  <!-- /wp:paragraph -->
</div></div>
<!-- /wp:media-text -->
