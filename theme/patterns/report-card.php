<?php
/**
 * Title: Report for download
 * Slug: aoc/report-card
 * Categories: aoc
 * Keywords: report, download, pdf, file, working paper
 * Description: A card with a report's title, date, short summary and a download button. Click the button and pick "Link" to choose a PDF from the Media Library.
 */
?>
<!-- wp:group {"className":"is-style-aoc-card aoc-report","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group is-style-aoc-card aoc-report" style="margin-bottom:var(--wp--preset--spacing--40)">
  <!-- wp:paragraph {"className":"aoc-eyebrow"} -->
  <p class="aoc-eyebrow">Report · November 2026</p>
  <!-- /wp:paragraph -->
  <!-- wp:heading {"level":3} -->
  <h3 class="wp-block-heading">Age of Creativity: first research report</h3>
  <!-- /wp:heading -->
  <!-- wp:paragraph -->
  <p>A short summary of what the report covers and who it is for. Two or three sentences is plenty.</p>
  <!-- /wp:paragraph -->
  <!-- wp:buttons -->
  <div class="wp-block-buttons">
    <!-- wp:button -->
    <div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url(get_theme_file_uri('assets/downloads/age-of-creativity-report.pdf')); ?>">Download PDF</a></div>
    <!-- /wp:button -->
  </div>
  <!-- /wp:buttons -->
</div>
<!-- /wp:group -->
