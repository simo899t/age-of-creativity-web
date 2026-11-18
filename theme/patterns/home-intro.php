<?php
/**
 * Title: Front page: about the project
 * Slug: aoc/home-intro
 * Categories: aoc
 * Description: A short introduction to the project with links onward. Used under the timeline on the front page.
 */
?>
<!-- wp:group {"tagName":"section","align":"full","className":"aoc-band","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"surface","layout":{"type":"constrained","contentSize":"76rem"}} -->
<section class="wp-block-group alignfull aoc-band has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
  <!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"0"}}}} -->
  <div class="wp-block-columns">
    <!-- wp:column {"width":"41.66%"} -->
    <div class="wp-block-column" style="flex-basis:41.66%">
      <!-- wp:paragraph {"className":"aoc-eyebrow"} -->
      <p class="aoc-eyebrow">About the project</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column {"width":"58.34%"} -->
    <div class="wp-block-column" style="flex-basis:58.34%">
      <!-- wp:paragraph {"className":"aoc-lead"} -->
      <p class="aoc-lead">Age of Creativity studies how creative work develops across a whole life, from first works to the last. We follow composers, artists, writers and scientists from the Renaissance to the twentieth century, and compare when and under what circumstances their work changed.</p>
      <!-- /wp:paragraph -->
      <!-- wp:paragraph {"className":"aoc-links"} -->
      <p class="aoc-links"><a href="<?php echo esc_url(home_url('/about/')); ?>">Read about the project</a> <a href="<?php echo esc_url(home_url('/output/publications/')); ?>">Publications</a> <a href="<?php echo esc_url(home_url('/team/')); ?>">The team</a></p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->
</section>
<!-- /wp:group -->
