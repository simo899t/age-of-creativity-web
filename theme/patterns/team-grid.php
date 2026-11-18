<?php
/**
 * Title: Team members
 * Slug: aoc/team-grid
 * Categories: aoc
 * Keywords: team, people, staff, portrait
 * Description: A heading and a grid of team members with photo, name, role and a short bio. Click a photo to replace it.
 */
?>
<!-- wp:group {"tagName":"section","style":{"spacing":{"margin":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"default"}} -->
<section class="wp-block-group" style="margin-top:var(--wp--preset--spacing--50);margin-bottom:var(--wp--preset--spacing--50)">
  <!-- wp:heading {"level":2} -->
  <h2 class="wp-block-heading">Research team</h2>
  <!-- /wp:heading -->
  <!-- wp:columns {"className":"aoc-people","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|40"},"margin":{"top":"var:preset|spacing|40"}}}} -->
  <div class="wp-block-columns aoc-people" style="margin-top:var(--wp--preset--spacing--40)">
      <!-- wp:column -->
      <div class="wp-block-column">
        <!-- wp:group {"className":"aoc-person","layout":{"type":"default"}} -->
        <div class="wp-block-group aoc-person">
          <!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"full","linkDestination":"none","className":"aoc-person__photo"} -->
          <figure class="wp-block-image size-full aoc-person__photo"><img src="<?php echo esc_url(get_theme_file_uri('assets/img/portrait-placeholder.svg')); ?>" alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
          <!-- /wp:image -->
          <!-- wp:heading {"level":3,"className":"aoc-person__name"} -->
          <h3 class="wp-block-heading aoc-person__name">Name Surname</h3>
          <!-- /wp:heading -->
          <!-- wp:paragraph {"className":"aoc-person__role"} -->
          <p class="aoc-person__role">Principal investigator</p>
          <!-- /wp:paragraph -->
          <!-- wp:paragraph {"fontSize":"small"} -->
          <p class="has-small-font-size">Two or three sentences about research interests and role in the project. <a href="mailto:name@sdu.dk">name@sdu.dk</a></p>
          <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->
      </div>
      <!-- /wp:column -->
      <!-- wp:column -->
      <div class="wp-block-column">
        <!-- wp:group {"className":"aoc-person","layout":{"type":"default"}} -->
        <div class="wp-block-group aoc-person">
          <!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"full","linkDestination":"none","className":"aoc-person__photo"} -->
          <figure class="wp-block-image size-full aoc-person__photo"><img src="<?php echo esc_url(get_theme_file_uri('assets/img/portrait-placeholder.svg')); ?>" alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
          <!-- /wp:image -->
          <!-- wp:heading {"level":3,"className":"aoc-person__name"} -->
          <h3 class="wp-block-heading aoc-person__name">Name Surname</h3>
          <!-- /wp:heading -->
          <!-- wp:paragraph {"className":"aoc-person__role"} -->
          <p class="aoc-person__role">Postdoctoral researcher</p>
          <!-- /wp:paragraph -->
          <!-- wp:paragraph {"fontSize":"small"} -->
          <p class="has-small-font-size">Two or three sentences about research interests and role in the project. <a href="mailto:name@sdu.dk">name@sdu.dk</a></p>
          <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->
      </div>
      <!-- /wp:column -->
      <!-- wp:column -->
      <div class="wp-block-column">
        <!-- wp:group {"className":"aoc-person","layout":{"type":"default"}} -->
        <div class="wp-block-group aoc-person">
          <!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"full","linkDestination":"none","className":"aoc-person__photo"} -->
          <figure class="wp-block-image size-full aoc-person__photo"><img src="<?php echo esc_url(get_theme_file_uri('assets/img/portrait-placeholder.svg')); ?>" alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
          <!-- /wp:image -->
          <!-- wp:heading {"level":3,"className":"aoc-person__name"} -->
          <h3 class="wp-block-heading aoc-person__name">Name Surname</h3>
          <!-- /wp:heading -->
          <!-- wp:paragraph {"className":"aoc-person__role"} -->
          <p class="aoc-person__role">PhD fellow</p>
          <!-- /wp:paragraph -->
          <!-- wp:paragraph {"fontSize":"small"} -->
          <p class="has-small-font-size">Two or three sentences about research interests and role in the project. <a href="mailto:name@sdu.dk">name@sdu.dk</a></p>
          <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->
      </div>
      <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->
  <!-- wp:columns {"className":"aoc-people","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|40"},"margin":{"top":"var:preset|spacing|40"}}}} -->
  <div class="wp-block-columns aoc-people" style="margin-top:var(--wp--preset--spacing--40)">
      <!-- wp:column -->
      <div class="wp-block-column">
        <!-- wp:group {"className":"aoc-person","layout":{"type":"default"}} -->
        <div class="wp-block-group aoc-person">
          <!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"full","linkDestination":"none","className":"aoc-person__photo"} -->
          <figure class="wp-block-image size-full aoc-person__photo"><img src="<?php echo esc_url(get_theme_file_uri('assets/img/portrait-placeholder.svg')); ?>" alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
          <!-- /wp:image -->
          <!-- wp:heading {"level":3,"className":"aoc-person__name"} -->
          <h3 class="wp-block-heading aoc-person__name">Name Surname</h3>
          <!-- /wp:heading -->
          <!-- wp:paragraph {"className":"aoc-person__role"} -->
          <p class="aoc-person__role">Research assistant</p>
          <!-- /wp:paragraph -->
          <!-- wp:paragraph {"fontSize":"small"} -->
          <p class="has-small-font-size">Two or three sentences about research interests and role in the project. <a href="mailto:name@sdu.dk">name@sdu.dk</a></p>
          <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->
      </div>
      <!-- /wp:column -->
      <!-- wp:column -->
      <div class="wp-block-column">
        <!-- wp:group {"className":"aoc-person","layout":{"type":"default"}} -->
        <div class="wp-block-group aoc-person">
          <!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"full","linkDestination":"none","className":"aoc-person__photo"} -->
          <figure class="wp-block-image size-full aoc-person__photo"><img src="<?php echo esc_url(get_theme_file_uri('assets/img/portrait-placeholder.svg')); ?>" alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
          <!-- /wp:image -->
          <!-- wp:heading {"level":3,"className":"aoc-person__name"} -->
          <h3 class="wp-block-heading aoc-person__name">Name Surname</h3>
          <!-- /wp:heading -->
          <!-- wp:paragraph {"className":"aoc-person__role"} -->
          <p class="aoc-person__role">Data manager</p>
          <!-- /wp:paragraph -->
          <!-- wp:paragraph {"fontSize":"small"} -->
          <p class="has-small-font-size">Two or three sentences about research interests and role in the project. <a href="mailto:name@sdu.dk">name@sdu.dk</a></p>
          <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->
      </div>
      <!-- /wp:column -->
      <!-- wp:column -->
      <div class="wp-block-column">
        <!-- wp:group {"className":"aoc-person","layout":{"type":"default"}} -->
        <div class="wp-block-group aoc-person">
          <!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"full","linkDestination":"none","className":"aoc-person__photo"} -->
          <figure class="wp-block-image size-full aoc-person__photo"><img src="<?php echo esc_url(get_theme_file_uri('assets/img/portrait-placeholder.svg')); ?>" alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
          <!-- /wp:image -->
          <!-- wp:heading {"level":3,"className":"aoc-person__name"} -->
          <h3 class="wp-block-heading aoc-person__name">Name Surname</h3>
          <!-- /wp:heading -->
          <!-- wp:paragraph {"className":"aoc-person__role"} -->
          <p class="aoc-person__role">Affiliated researcher</p>
          <!-- /wp:paragraph -->
          <!-- wp:paragraph {"fontSize":"small"} -->
          <p class="has-small-font-size">Two or three sentences about research interests and role in the project. <a href="mailto:name@sdu.dk">name@sdu.dk</a></p>
          <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->
      </div>
      <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->
</section>
<!-- /wp:group -->
