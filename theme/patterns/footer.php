<?php
/**
 * Title: Footer
 * Slug: aoc/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Inserter: no
 */
?>
<!-- wp:group {"tagName":"div","className":"aoc-footer","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"},"margin":{"top":"0"}}},"backgroundColor":"tint","layout":{"type":"constrained","contentSize":"76rem"}} -->
<div class="wp-block-group alignfull aoc-footer has-tint-background-color has-background" style="margin-top:0;padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
  <!-- wp:group {"className":"aoc-footer__row","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
  <div class="wp-block-group aoc-footer__row">
    <!-- wp:group {"className":"aoc-footer__about","style":{"spacing":{"blockGap":"0.35rem"}},"layout":{"type":"flex","orientation":"vertical"}} -->
    <div class="wp-block-group aoc-footer__about">
      <!-- wp:site-title {"level":0,"style":{"typography":{"fontSize":"1.35rem","lineHeight":"1.1"}}} /-->
      <!-- wp:paragraph {"className":"aoc-footer__small"} -->
      <p class="aoc-footer__small">A research project at the University of Southern Denmark, funded by the Carlsberg Foundation.</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->

    <!-- wp:image {"width":"112px","sizeSlug":"full","linkDestination":"custom","className":"aoc-footer__logo"} -->
    <figure class="wp-block-image size-full is-resized aoc-footer__logo"><a href="https://www.sdu.dk"><img src="<?php echo esc_url(get_theme_file_uri('assets/img/sdu-logo.png')); ?>" alt="University of Southern Denmark" style="width:112px"/></a></figure>
    <!-- /wp:image -->
  </div>
  <!-- /wp:group -->
</div>
<!-- /wp:group -->
