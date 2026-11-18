<?php
/**
 * Title: Footer
 * Slug: aoc/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Inserter: no
 */
?>
<!-- wp:group {"tagName":"div","className":"aoc-footer","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|40"},"margin":{"top":"0"}}},"backgroundColor":"ink","textColor":"paper","layout":{"type":"constrained","contentSize":"76rem"}} -->
<div class="wp-block-group alignfull aoc-footer has-paper-color has-ink-background-color has-text-color has-background" style="margin-top:0;padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--40)">

  <!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|50"}}}} -->
  <div class="wp-block-columns">
    <!-- wp:column {"width":"45%"} -->
    <div class="wp-block-column" style="flex-basis:45%">
      <!-- wp:site-title {"level":0,"style":{"typography":{"fontSize":"1.9rem","lineHeight":"1.1"}}} /-->
      <!-- wp:site-tagline {"style":{"typography":{"fontStyle":"italic"}},"className":"aoc-footer__tagline"} /-->
      <!-- wp:paragraph {"className":"aoc-footer__small"} -->
      <p class="aoc-footer__small">A research project at the University of Southern Denmark, funded by the Carlsberg Foundation.</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column {"width":"30%"} -->
    <div class="wp-block-column" style="flex-basis:30%">
      <!-- wp:heading {"level":2,"className":"aoc-footer__label"} -->
      <h2 class="wp-block-heading aoc-footer__label">Explore</h2>
      <!-- /wp:heading -->
      <!-- wp:navigation {"overlayMenu":"never","className":"aoc-footer__nav","layout":{"type":"flex","orientation":"vertical"}} /-->
    </div>
    <!-- /wp:column -->

    <!-- wp:column {"width":"25%"} -->
    <div class="wp-block-column" style="flex-basis:25%">
      <!-- wp:heading {"level":2,"className":"aoc-footer__label"} -->
      <h2 class="wp-block-heading aoc-footer__label">Hosted by</h2>
      <!-- /wp:heading -->
      <!-- wp:image {"width":"140px","sizeSlug":"full","linkDestination":"custom","className":"aoc-footer__logo"} -->
      <figure class="wp-block-image size-full is-resized aoc-footer__logo"><a href="https://www.sdu.dk"><img src="<?php echo esc_url(get_theme_file_uri('assets/img/sdu-logo.png')); ?>" alt="University of Southern Denmark" style="width:140px"/></a></figure>
      <!-- /wp:image -->
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->

  <!-- wp:separator {"className":"aoc-footer__rule","style":{"spacing":{"margin":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}}} -->
  <hr class="wp-block-separator has-alpha-channel-opacity aoc-footer__rule" style="margin-top:var(--wp--preset--spacing--50);margin-bottom:var(--wp--preset--spacing--30)"/>
  <!-- /wp:separator -->

  <!-- wp:paragraph {"className":"aoc-footer__small"} -->
  <p class="aoc-footer__small">© Age of Creativity Research Project</p>
  <!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
