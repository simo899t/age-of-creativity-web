<?php
/**
 * Title: Contact details
 * Slug: aoc/contact
 * Categories: aoc
 * Keywords: contact, address, email, phone
 * Description: Contact person and postal address side by side.
 */
?>
<!-- wp:columns {"className":"aoc-contact","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|50"},"margin":{"top":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns aoc-contact" style="margin-top:var(--wp--preset--spacing--40)">
  <!-- wp:column -->
  <div class="wp-block-column">
    <!-- wp:group {"className":"is-style-aoc-card","layout":{"type":"default"}} -->
    <div class="wp-block-group is-style-aoc-card">
      <!-- wp:paragraph {"className":"aoc-eyebrow"} -->
      <p class="aoc-eyebrow">Contact person</p>
      <!-- /wp:paragraph -->
      <!-- wp:heading {"level":3} -->
      <h3 class="wp-block-heading">Name Surname</h3>
      <!-- /wp:heading -->
      <!-- wp:paragraph -->
      <p>Principal investigator<br><a href="mailto:name@sdu.dk">name@sdu.dk</a><br>+45 0000 0000</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->
  </div>
  <!-- /wp:column -->
  <!-- wp:column -->
  <div class="wp-block-column">
    <!-- wp:group {"className":"is-style-aoc-card","layout":{"type":"default"}} -->
    <div class="wp-block-group is-style-aoc-card">
      <!-- wp:paragraph {"className":"aoc-eyebrow"} -->
      <p class="aoc-eyebrow">Address</p>
      <!-- /wp:paragraph -->
      <!-- wp:heading {"level":3} -->
      <h3 class="wp-block-heading">University of Southern Denmark</h3>
      <!-- /wp:heading -->
      <!-- wp:paragraph -->
      <p>Department name<br>Campusvej 55<br>5230 Odense M<br>Denmark</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->
  </div>
  <!-- /wp:column -->
</div>
<!-- /wp:columns -->
