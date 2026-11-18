<?php
/**
 * Title: Front page: about the project
 * Slug: aoc/home-intro
 * Categories: aoc
 * Description: Short introduction with links to the main sections. Used under the timeline on the front page.
 */
?>
<!-- wp:group {"tagName":"section","align":"full","className":"aoc-band","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"backgroundColor":"surface","layout":{"type":"constrained","contentSize":"76rem"}} -->
<section class="wp-block-group alignfull aoc-band has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">
  <!-- wp:columns {"verticalAlignment":"top","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
  <div class="wp-block-columns are-vertically-aligned-top">
    <!-- wp:column {"verticalAlignment":"top","width":"40%"} -->
    <div class="wp-block-column is-vertically-aligned-top" style="flex-basis:40%">
      <!-- wp:paragraph {"className":"aoc-eyebrow"} -->
      <p class="aoc-eyebrow">The project</p>
      <!-- /wp:paragraph -->
      <!-- wp:heading {"level":2,"fontSize":"xx-large"} -->
      <h2 class="wp-block-heading has-xx-large-font-size">Does creativity fade, or does it change?</h2>
      <!-- /wp:heading -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column {"verticalAlignment":"top","width":"60%"} -->
    <div class="wp-block-column is-vertically-aligned-top" style="flex-basis:60%">
      <!-- wp:paragraph {"className":"aoc-lead"} -->
      <p class="aoc-lead">Age of Creativity studies how creative work develops across a whole life, from first works to the last. We follow composers, artists, writers and scientists from the Renaissance to the twentieth century.</p>
      <!-- /wp:paragraph -->
      <!-- wp:paragraph -->
      <p>Many of the people on the timeline above produced some of their most radical work late in life, often in new circumstances: a new patron, a new audience, failing health, or freedom from earlier expectations. The project brings these life courses together in one database so that patterns across fields and centuries can be compared.</p>
      <!-- /wp:paragraph -->
      <!-- wp:buttons -->
      <div class="wp-block-buttons">
        <!-- wp:button -->
        <div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url(home_url('/about/')); ?>">About the project</a></div>
        <!-- /wp:button -->
        <!-- wp:button {"className":"is-style-aoc-quiet"} -->
        <div class="wp-block-button is-style-aoc-quiet"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url(home_url('/team/')); ?>">Meet the team</a></div>
        <!-- /wp:button -->
      </div>
      <!-- /wp:buttons -->
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->
</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained","contentSize":"76rem"}} -->
<section class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">
  <!-- wp:paragraph {"className":"aoc-eyebrow"} -->
  <p class="aoc-eyebrow">Output</p>
  <!-- /wp:paragraph -->
  <!-- wp:heading {"level":2} -->
  <h2 class="wp-block-heading">What the project produces</h2>
  <!-- /wp:heading -->

  <!-- wp:columns {"className":"aoc-cards","style":{"spacing":{"margin":{"top":"var:preset|spacing|50"},"blockGap":{"left":"var:preset|spacing|40"}}}} -->
  <div class="wp-block-columns aoc-cards" style="margin-top:var(--wp--preset--spacing--50)">
    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"is-style-aoc-card","layout":{"type":"default"}} -->
      <div class="wp-block-group is-style-aoc-card">
        <!-- wp:paragraph {"className":"aoc-card-number"} -->
        <p class="aoc-card-number">01</p>
        <!-- /wp:paragraph -->
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading"><a href="<?php echo esc_url(home_url('/output/database/')); ?>">Database</a></h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph -->
        <p>Life courses and works of creators across music, art, literature and science.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"is-style-aoc-card","layout":{"type":"default"}} -->
      <div class="wp-block-group is-style-aoc-card">
        <!-- wp:paragraph {"className":"aoc-card-number"} -->
        <p class="aoc-card-number">02</p>
        <!-- /wp:paragraph -->
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading"><a href="<?php echo esc_url(home_url('/output/publications/')); ?>">Publications</a></h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph -->
        <p>Articles, chapters and books by the project team.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"is-style-aoc-card","layout":{"type":"default"}} -->
      <div class="wp-block-group is-style-aoc-card">
        <!-- wp:paragraph {"className":"aoc-card-number"} -->
        <p class="aoc-card-number">03</p>
        <!-- /wp:paragraph -->
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading"><a href="<?php echo esc_url(home_url('/output/reports/')); ?>">Reports</a></h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph -->
        <p>Working papers and reports for download.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->
</section>
<!-- /wp:group -->
