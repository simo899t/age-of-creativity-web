<?php
/**
 * Title: Event
 * Slug: aoc/event
 * Categories: aoc
 * Keywords: event, seminar, conference, workshop, date, programme
 * Description: An event with date, time, place and a registration button, followed by a simple programme.
 */
?>
<!-- wp:group {"tagName":"section","className":"aoc-event","style":{"spacing":{"margin":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"default"}} -->
<section class="wp-block-group aoc-event" style="margin-top:var(--wp--preset--spacing--50);margin-bottom:var(--wp--preset--spacing--50)">
  <!-- wp:group {"className":"is-style-aoc-card","layout":{"type":"default"}} -->
  <div class="wp-block-group is-style-aoc-card">
    <!-- wp:paragraph {"className":"aoc-eyebrow"} -->
    <p class="aoc-eyebrow">Symposium</p>
    <!-- /wp:paragraph -->
    <!-- wp:heading {"level":2} -->
    <h2 class="wp-block-heading">Late Style: Creativity in the Final Decades</h2>
    <!-- /wp:heading -->
    <!-- wp:columns {"className":"aoc-facts"} -->
    <div class="wp-block-columns aoc-facts">
      <!-- wp:column -->
      <div class="wp-block-column">
        <!-- wp:paragraph {"className":"aoc-fact"} -->
        <p class="aoc-fact"><strong>Date</strong>Thursday 4 June 2026</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:column -->
      <!-- wp:column -->
      <div class="wp-block-column">
        <!-- wp:paragraph {"className":"aoc-fact"} -->
        <p class="aoc-fact"><strong>Time</strong>10:00–16:00</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:column -->
      <!-- wp:column -->
      <div class="wp-block-column">
        <!-- wp:paragraph {"className":"aoc-fact"} -->
        <p class="aoc-fact"><strong>Place</strong>University of Southern Denmark, Odense</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
    <!-- wp:paragraph -->
    <p>A short description of the event: what it is about, who is speaking and who should come.</p>
    <!-- /wp:paragraph -->
    <!-- wp:buttons -->
    <div class="wp-block-buttons">
      <!-- wp:button -->
      <div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#">Register</a></div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
  </div>
  <!-- /wp:group -->

  <!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
  <h3 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--50)">Programme</h3>
  <!-- /wp:heading -->
  <!-- wp:table {"className":"is-style-regular aoc-programme"} -->
  <figure class="wp-block-table is-style-regular aoc-programme"><table><tbody><tr><td>10:00</td><td>Welcome and introduction</td></tr><tr><td>10:30</td><td>Keynote: speaker name, title of talk</td></tr><tr><td>12:00</td><td>Lunch</td></tr><tr><td>13:00</td><td>Panel discussion</td></tr><tr><td>15:30</td><td>Closing remarks</td></tr></tbody></table></figure>
  <!-- /wp:table -->
</section>
<!-- /wp:group -->
