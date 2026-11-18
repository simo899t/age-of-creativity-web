<?php
/**
 * Title: Event
 * Slug: aoc/event
 * Categories: aoc
 * Keywords: event, seminar, conference, workshop, date, programme
 * Description: An event: the date set large on the left, then title, time, place, a short description, a registration link and a simple programme.
 */
?>
<!-- wp:group {"tagName":"section","align":"wide","className":"aoc-event","style":{"spacing":{"margin":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|60"}}},"layout":{"type":"default"}} -->
<section class="wp-block-group alignwide aoc-event" style="margin-top:var(--wp--preset--spacing--50);margin-bottom:var(--wp--preset--spacing--60)">
  <!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"0"}}}} -->
  <div class="wp-block-columns">
    <!-- wp:column {"width":"41.66%"} -->
    <div class="wp-block-column" style="flex-basis:41.66%">
      <!-- wp:paragraph {"className":"aoc-event__date"} -->
      <p class="aoc-event__date">4 June</p>
      <!-- /wp:paragraph -->
      <!-- wp:paragraph -->
      <p>Thursday, 2026</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column {"width":"58.34%"} -->
    <div class="wp-block-column" style="flex-basis:58.34%">
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
        <!-- wp:button {"className":"is-style-aoc-quiet"} -->
        <div class="wp-block-button is-style-aoc-quiet"><a class="wp-block-button__link wp-element-button" href="#">Register</a></div>
        <!-- /wp:button -->
      </div>
      <!-- /wp:buttons -->

      <!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
      <h3 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--50)">Programme</h3>
      <!-- /wp:heading -->
      <!-- wp:table {"className":"is-style-regular aoc-programme"} -->
      <figure class="wp-block-table is-style-regular aoc-programme"><table><tbody><tr><td>10:00</td><td>Welcome and introduction</td></tr><tr><td>10:30</td><td>Keynote: speaker name, title of talk</td></tr><tr><td>12:00</td><td>Lunch</td></tr><tr><td>13:00</td><td>Panel discussion</td></tr><tr><td>15:30</td><td>Closing remarks</td></tr></tbody></table></figure>
      <!-- /wp:table -->
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->
</section>
<!-- /wp:group -->
