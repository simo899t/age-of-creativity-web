<?php
/**
 * Server render of the Creators timeline.
 *
 * Prints the intro, the field filter and a plain list of every creator.
 * view.js turns the list into the interactive timeline; without JavaScript
 * (or for search engines and screen readers) the list is still a complete,
 * readable index of the people.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

if (!defined('ABSPATH')) {
    exit;
}

$data   = aoc_timeline_data();
$fields = [];
foreach ($data['fields'] as $f) {
    $fields[$f['slug']] = $f;
}
$counts = array_count_values(array_filter(array_column($data['people'], 'field')));

$title    = get_bloginfo('name');
$question = get_bloginfo('description');
// Set the small connecting words of the title in italics ("Age *of* Creativity").
$title_html = preg_replace(
    '/(?<=\s)(of|and|the|in|on|for|&amp;)(?=\s)/i',
    '<em>$1</em>',
    esc_html($title)
);

$wrapper = get_block_wrapper_attributes([
    'class'                => 'aoc-tl',
    'data-aoc-timeline'    => '',
    'aria-label'           => __('Timeline of creators', 'aoc'),
]);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
  <?php if (!empty($attributes['showTitle'])) : ?>
    <header class="aoc-tl-intro">
      <?php if (!empty($attributes['kicker'])) : ?>
        <p class="aoc-tl-kicker"><?php echo esc_html($attributes['kicker']); ?></p>
      <?php endif; ?>
      <h1 class="aoc-tl-title"><?php echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above ?></h1>
      <?php if ($question) : ?>
        <p class="aoc-tl-question"><?php echo esc_html($question); ?></p>
      <?php endif; ?>
    </header>
  <?php endif; ?>

  <div class="aoc-tl-toolbar">
    <div class="aoc-tl-fields" role="group" aria-label="<?php esc_attr_e('Filter by field', 'aoc'); ?>">
      <button type="button" class="aoc-tl-field" data-field="" aria-pressed="true">
        <?php esc_html_e('All fields', 'aoc'); ?> <span class="aoc-tl-count"><?php echo count($data['people']); ?></span>
      </button>
      <?php foreach ($data['fields'] as $f) : ?>
        <button type="button" class="aoc-tl-field" data-field="<?php echo esc_attr($f['slug']); ?>" aria-pressed="false" style="--aoc-field:<?php echo esc_attr($f['color']); ?>">
          <span class="aoc-tl-swatch" aria-hidden="true"></span><?php echo esc_html($f['name']); ?>
          <span class="aoc-tl-count"><?php echo (int) ($counts[$f['slug']] ?? 0); ?></span>
        </button>
      <?php endforeach; ?>
    </div>
    <label class="aoc-tl-search">
      <span class="screen-reader-text"><?php esc_html_e('Find a person', 'aoc'); ?></span>
      <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      <input type="search" placeholder="<?php esc_attr_e('Find a person or work…', 'aoc'); ?>" autocomplete="off">
    </label>
  </div>

  <div class="aoc-tl-stage">
    <div class="aoc-tl-canvas">
      <ol class="aoc-tl-fallback">
        <?php foreach ($data['people'] as $p) : ?>
          <li>
            <strong><?php echo esc_html($p['name']); ?></strong>
            (<?php echo esc_html($p['born'] . '–' . ($p['died'] ?? '')); ?>)<?php
            if (isset($fields[$p['field']])) {
                echo ', ' . esc_html($fields[$p['field']]['name']);
            } ?>.
            <?php echo esc_html($p['description']); ?>
          </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </div>

  <div class="aoc-tl-detail" aria-live="polite">
    <p class="aoc-tl-hint"><?php echo esc_html($attributes['hint'] ?? ''); ?></p>
  </div>

  <script type="application/json" class="aoc-tl-data"><?php echo wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?></script>
</section>
