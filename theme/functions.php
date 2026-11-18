<?php

add_action('after_setup_theme', function () {
    add_theme_support('custom-logo', [
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    // The front page's subtitle is the page excerpt (see
    // templates/front-page.html), so pages need the Excerpt panel.
    add_post_type_support('page', 'excerpt');
});

// enqueue_block_assets (unlike wp_enqueue_scripts) also fires inside the
// block editor's canvas, so the Site Editor and page editor show the real
// design instead of unstyled blocks.
add_action('enqueue_block_assets', function () {
    $theme_dir = get_stylesheet_directory();
    wp_enqueue_style(
        'aoc-theme',
        get_stylesheet_directory_uri() . '/assets/css/theme.css',
        [],
        filemtime($theme_dir . '/assets/css/theme.css')
    );
});

add_action('wp_enqueue_scripts', function () {
    $theme_uri = get_stylesheet_directory_uri();
    $theme_dir = get_stylesheet_directory();

    if (is_page_template('page-cite.html') || is_page('cite')) {
        wp_enqueue_script(
            'aoc-copy-citation',
            $theme_uri . '/assets/js/copy-citation.js',
            [],
            filemtime($theme_dir . '/assets/js/copy-citation.js'),
            true
        );
    }

    // Footer is position:fixed on every page (see theme.css); this measures
    // its real height into a CSS var so page content reserves the right
    // amount of space instead of running underneath it.
    wp_enqueue_script(
        'aoc-fixed-bars',
        $theme_uri . '/assets/js/fixed-bars.js',
        [],
        filemtime($theme_dir . '/assets/js/fixed-bars.js'),
        true
    );
});

// Banner image + credit. A page with no featured image of its own still
// gets the default painting instead of an empty space (setting a featured
// image in the editor sidebar replaces it). The credit in the image's corner
// is the image's Caption from the Media Library, so a new image brings its
// own credit; an image with no caption shows none.
const AOC_DEFAULT_CREDIT = 'Joseph Wright of Derby, <em>A Philosopher Lecturing on the Orrery</em>, c. 1766 &middot; Public domain';

add_filter('render_block_core/post-featured-image', function ($html, $block, $instance) {
    if (is_admin()) {
        return $html;
    }
    if (trim($html) === '') {
        $src = esc_url(get_stylesheet_directory_uri() . '/assets/img/orrery.jpg');
        $alt = esc_attr__('Joseph Wright of Derby, A Philosopher Lecturing on the Orrery, c. 1766');
        $img = '<img src="' . $src . '" alt="' . $alt . '" />';
        return '<figure class="wp-block-post-featured-image">' . $img . aoc_credit(AOC_DEFAULT_CREDIT) . '</figure>';
    }
    $post_id = $instance->context['postId'] ?? get_the_ID();
    $caption = wp_get_attachment_caption(get_post_thumbnail_id($post_id));
    if (!$caption) {
        return $html;
    }
    return preg_replace('#</figure>\s*$#', aoc_credit(wp_kses_post($caption)) . '</figure>', $html, 1);
}, 10, 3);

function aoc_credit($text) {
    return '<span class="aoc-hero-credit">' . $text . '</span>';
}
