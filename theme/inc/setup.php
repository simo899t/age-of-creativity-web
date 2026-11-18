<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', function () {
    add_theme_support('editor-styles');
    add_editor_style('assets/css/theme.css');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', ['flex-height' => true, 'flex-width' => true]);
    // A page's excerpt is shown as the lead paragraph under its title.
    add_post_type_support('page', 'excerpt');
    remove_theme_support('core-block-patterns');
});

add_action('init', function () {
    register_block_type(get_theme_file_path('blocks/timeline'));

    register_block_pattern_category('aoc', [
        'label'       => __('Age of Creativity', 'aoc'),
        'description' => __('Ready-made sections for this site.', 'aoc'),
    ]);

    // Separator line used in the header and between sections.
    register_block_style('core/separator', [
        'name'  => 'aoc-short',
        'label' => __('Short', 'aoc'),
    ]);
    register_block_style('core/group', [
        'name'  => 'aoc-card',
        'label' => __('Ruled', 'aoc'),
    ]);
    register_block_style('core/list', [
        'name'  => 'aoc-plain',
        'label' => __('No bullets', 'aoc'),
    ]);
    register_block_style('core/button', [
        'name'  => 'aoc-quiet',
        'label' => __('Quiet', 'aoc'),
    ]);
});

add_action('wp_enqueue_scripts', function () {
    $file = get_theme_file_path('assets/css/theme.css');
    wp_enqueue_style('aoc-theme', get_theme_file_uri('assets/css/theme.css'), [], filemtime($file));

    if (is_page_template('page-cite')) {
        wp_enqueue_script('aoc-copy-citation', get_theme_file_uri('assets/js/copy-citation.js'), [], filemtime(get_theme_file_path('assets/js/copy-citation.js')), true);
    }
});

// Give the timeline block's editor card links into the dashboard and the creator count.
add_action('enqueue_block_editor_assets', function () {
    $counts = wp_count_posts(AOC_CREATOR);
    wp_add_inline_script(
        'aoc-timeline-editor-script',
        'window.aocTimelineEditor = ' . wp_json_encode([
            'admin' => admin_url(),
            'count' => (int) ($counts->publish ?? 0),
        ]) . ';',
        'before'
    );
});

// Preload the two fonts used above the fold.
add_action('wp_head', function () {
    foreach (['fraunces.woff2', 'inter.woff2'] as $font) {
        printf('<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url(get_theme_file_uri('assets/fonts/' . $font)));
    }
}, 1);

add_filter('body_class', function ($classes) {
    if (is_front_page()) {
        $classes[] = 'aoc-is-home';
    }
    return $classes;
});

/* Excerpt "more" marker: plain ellipsis instead of [...] */
add_filter('excerpt_more', fn() => '…');

/* Dashboard: point editors at the help page for this theme. */
add_action('admin_notices', function () {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'dashboard') {
        return;
    }
    echo '<div class="notice notice-info"><p>';
    printf(
        /* translators: 1: Pages link, 2: Creators link */
        esc_html__('Edit the site’s text and pictures under %1$s. The people on the front-page timeline are under %2$s.', 'aoc'),
        '<a href="' . esc_url(admin_url('edit.php?post_type=page')) . '">' . esc_html__('Pages', 'aoc') . '</a>',
        '<a href="' . esc_url(admin_url('edit.php?post_type=' . AOC_CREATOR)) . '">' . esc_html__('Creators', 'aoc') . '</a>'
    );
    echo '</p></div>';
});
