<?php
/**
 * Title: Highlighted note
 * Slug: aoc/callout
 * Categories: aoc
 * Keywords: note, highlight, box, notice, callout
 * Description: A tinted box for something readers should not miss.
 */
?>
<!-- wp:group {"className":"aoc-callout","backgroundColor":"tint","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group aoc-callout has-tint-background-color has-background" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
  <!-- wp:paragraph {"className":"aoc-eyebrow"} -->
  <p class="aoc-eyebrow">Note</p>
  <!-- /wp:paragraph -->
  <!-- wp:paragraph -->
  <p>Something readers should notice: a deadline, a call for participants, or a change of plans.</p>
  <!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
