<?php
/**
 * Title: Publication list
 * Slug: aoc/publication-list
 * Categories: aoc
 * Keywords: publications, articles, bibliography, references, doi
 * Description: A year heading followed by publications with authors, title, where it was published and links. Duplicate an entry to add more.
 */
$entry = function ($authors, $title, $venue) {
    return '
  <!-- wp:group {"className":"aoc-pub","layout":{"type":"default"}} -->
  <div class="wp-block-group aoc-pub">
    <!-- wp:paragraph {"className":"aoc-pub__authors"} -->
    <p class="aoc-pub__authors">' . $authors . '</p>
    <!-- /wp:paragraph -->
    <!-- wp:heading {"level":3,"className":"aoc-pub__title"} -->
    <h3 class="wp-block-heading aoc-pub__title">' . $title . '</h3>
    <!-- /wp:heading -->
    <!-- wp:paragraph {"className":"aoc-pub__venue"} -->
    <p class="aoc-pub__venue">' . $venue . '</p>
    <!-- /wp:paragraph -->
    <!-- wp:buttons {"className":"aoc-pub__links"} -->
    <div class="wp-block-buttons aoc-pub__links">
      <!-- wp:button {"className":"is-style-aoc-quiet"} -->
      <div class="wp-block-button is-style-aoc-quiet"><a class="wp-block-button__link wp-element-button" href="https://doi.org/">Read (DOI)</a></div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
  </div>
  <!-- /wp:group -->
';
};
?>
<!-- wp:group {"tagName":"section","className":"aoc-pub-list","layout":{"type":"default"}} -->
<section class="wp-block-group aoc-pub-list">
  <!-- wp:heading {"level":2,"className":"aoc-year"} -->
  <h2 class="wp-block-heading aoc-year">2026</h2>
  <!-- /wp:heading -->
<?php
echo $entry('Surname, A. &amp; Surname, B.', 'Title of the journal article', '<em>Journal Name</em>, 12(3), 45–67.');
echo $entry('Surname, A.', 'Title of the book chapter', 'In C. Editor (Ed.), <em>Title of the Edited Book</em> (pp. 101–120). Publisher.');
?>
  <!-- wp:heading {"level":2,"className":"aoc-year"} -->
  <h2 class="wp-block-heading aoc-year">2025</h2>
  <!-- /wp:heading -->
<?php
echo $entry('Surname, B., Surname, C. &amp; Surname, A.', 'Title of the conference paper', '<em>Proceedings of the Conference Name</em>, City.');
?>
</section>
<!-- /wp:group -->
