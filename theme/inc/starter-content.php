<?php
/**
 * First-run content.
 *
 * When the theme is activated it fills in whatever is missing, and never
 * overwrites anything that already exists:
 *  - the four fields and the starter creators (only if there are no creators yet)
 *  - the pages: Home, About, Team, Output (Database, Publications, Reports), Events, Contact
 *  - a navigation menu linking them (only if the site has no menu yet)
 *  - the static front page, site title and tagline (only if still WordPress defaults)
 *
 * It can be run again from Appearance → Starter content, e.g. after deleting
 * a page by mistake.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('after_switch_theme', 'aoc_install_starter_content');

add_action('admin_menu', function () {
    add_theme_page(
        __('Starter content', 'aoc'),
        __('Starter content', 'aoc'),
        'manage_options',
        'aoc-starter',
        function () {
            $done = isset($_GET['done']);
            ?>
            <div class="wrap">
              <h1><?php esc_html_e('Starter content', 'aoc'); ?></h1>
              <?php if ($done) : ?>
                <div class="notice notice-success"><p><?php esc_html_e('Done. Anything that was missing has been added.', 'aoc'); ?></p></div>
              <?php endif; ?>
              <p><?php esc_html_e('Adds the standard pages, menu, fields and example creators if they are missing. Nothing that already exists is changed or deleted.', 'aoc'); ?></p>
              <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="aoc_starter">
                <?php wp_nonce_field('aoc_starter'); ?>
                <?php submit_button(__('Add missing starter content', 'aoc'), 'secondary'); ?>
              </form>
            </div>
            <?php
        }
    );
});

add_action('admin_post_aoc_starter', function () {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Sorry, you are not allowed to do that.', 'aoc'));
    }
    check_admin_referer('aoc_starter');
    aoc_install_starter_content();
    wp_safe_redirect(admin_url('themes.php?page=aoc-starter&done=1'));
    exit;
});

function aoc_render_pattern($slug) {
    $file = get_theme_file_path('patterns/' . $slug . '.php');
    if (!file_exists($file)) {
        return '';
    }
    ob_start();
    include $file;
    return trim(ob_get_clean());
}

function aoc_install_starter_content() {
    // The post type and taxonomy are registered on init; make sure they exist
    // when this runs from after_switch_theme.
    if (!post_type_exists(AOC_CREATOR)) {
        return;
    }
    aoc_install_creators();
    $pages = aoc_install_pages();
    aoc_install_navigation($pages);

    if (in_array(get_option('blogname'), ['', 'WordPress', 'My WordPress Website', 'My Blog'], true)) {
        update_option('blogname', 'Age of Creativity');
    }
    if (in_array(get_option('blogdescription'), ['', 'Just another WordPress site'], true)) {
        update_option('blogdescription', 'How does creativity evolve across the life course?');
    }
    if (!empty($pages['home']) && (get_option('show_on_front') !== 'page' || !get_post(get_option('page_on_front')))) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $pages['home']);
    }
}

function aoc_starter_data() {
    $json = file_get_contents(get_theme_file_path('inc/data/starter-creators.json'));
    return json_decode($json, true);
}

function aoc_install_creators() {
    $data = aoc_starter_data();

    if (!get_option('aoc_timeline')) {
        update_option('aoc_timeline', [
            'start'   => 1400,
            'end'     => 1950,
            'periods' => aoc_format_eras($data['periods']),
        ]);
    }

    $field_ids = [];
    foreach ($data['fields'] as $f) {
        $term = get_term_by('slug', $f['slug'], AOC_FIELD);
        if (!$term) {
            $res = wp_insert_term($f['name'], AOC_FIELD, ['slug' => $f['slug']]);
            if (is_wp_error($res)) {
                continue;
            }
            $id = $res['term_id'];
            update_term_meta($id, 'aoc_color', $f['color']);
            update_term_meta($id, 'aoc_order', $f['order']);
            update_term_meta($id, 'aoc_eras', aoc_format_eras($f['eras']));
        } else {
            $id = $term->term_id;
        }
        $field_ids[$f['slug']] = (int) $id;
    }

    $existing = get_posts(['post_type' => AOC_CREATOR, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids']);
    if ($existing) {
        return;
    }

    $ids = [];
    foreach ($data['creators'] as $c) {
        $id = wp_insert_post([
            'post_type'    => AOC_CREATOR,
            'post_status'  => 'publish',
            'post_title'   => $c['name'],
            'post_name'    => $c['slug'],
            'post_excerpt' => $c['description'],
        ]);
        if (!$id || is_wp_error($id)) {
            continue;
        }
        update_post_meta($id, 'aoc_born', (int) $c['born']);
        if ($c['died'] !== null) {
            update_post_meta($id, 'aoc_died', (int) $c['died']);
        }
        if (isset($field_ids[$c['field']])) {
            wp_set_object_terms($id, [$field_ids[$c['field']]], AOC_FIELD);
        }
        $ids[$c['slug']] = $id;
    }
    foreach ($data['creators'] as $c) {
        if (empty($ids[$c['slug']])) {
            continue;
        }
        $conn = [];
        foreach ($c['connections'] as $slug) {
            if (!empty($ids[$slug])) {
                $conn[] = $ids[$slug];
            }
        }
        update_post_meta($ids[$c['slug']], 'aoc_connections', $conn);
    }
}

/** One team member: photo, name, role, short bio (used by the Team pattern and page). */
function aoc_person_block($name, $role, $bio, $img = '') {
    $img = $img ?: get_theme_file_uri('assets/img/portrait-placeholder.svg');
    return '
  <!-- wp:group {"className":"aoc-person","layout":{"type":"default"}} -->
  <div class="wp-block-group aoc-person">
    <!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"full","linkDestination":"none","className":"aoc-person__photo"} -->
    <figure class="wp-block-image size-full aoc-person__photo"><img src="' . esc_url($img) . '" alt="' . esc_attr($img === get_theme_file_uri('assets/img/portrait-placeholder.svg') ? '' : $name) . '" style="aspect-ratio:1;object-fit:cover"/></figure>
    <!-- /wp:image -->
    <!-- wp:heading {"level":3,"className":"aoc-person__name"} -->
    <h3 class="wp-block-heading aoc-person__name">' . esc_html($name) . '</h3>
    <!-- /wp:heading -->
    <!-- wp:paragraph {"className":"aoc-person__role"} -->
    <p class="aoc-person__role">' . esc_html($role) . '</p>
    <!-- /wp:paragraph -->
    <!-- wp:paragraph {"className":"aoc-person__bio"} -->
    <p class="aoc-person__bio">' . $bio . '</p>
    <!-- /wp:paragraph -->
  </div>
  <!-- /wp:group -->
';
}

function aoc_team_page_content() {
    $bio = 'Two or three sentences about research interests and role in the project.';
    return '<!-- wp:group {"className":"aoc-people","layout":{"type":"grid","minimumColumnWidth":"19rem"}} -->
<div class="wp-block-group aoc-people">'
        . aoc_person_block('Name Surname', 'Principal investigator', $bio)
        . aoc_person_block('Name Surname', 'Postdoctoral researcher', $bio)
        . aoc_person_block('Name Surname', 'PhD fellow', $bio)
        . aoc_person_block('Simon Holm', 'Student assistant', 'Student assistant on the Age of Creativity project, working on the creators database and this website.', get_theme_file_uri('assets/img/team/simon.jpg'))
        . '</div>
<!-- /wp:group -->';
}

function aoc_block_p($text, $class = '') {
    $attrs = $class ? ' {"className":"' . $class . '"}' : '';
    $cls   = $class ? ' class="' . $class . '"' : '';
    return "<!-- wp:paragraph$attrs -->\n<p$cls>$text</p>\n<!-- /wp:paragraph -->\n\n";
}

function aoc_block_h($text, $level = 2) {
    $attrs = $level === 2 ? '' : ' {"level":' . $level . '}';
    return "<!-- wp:heading$attrs -->\n<h$level class=\"wp-block-heading\">$text</h$level>\n<!-- /wp:heading -->\n\n";
}

function aoc_block_button($label, $url) {
    return "<!-- wp:buttons -->\n<div class=\"wp-block-buttons\"><!-- wp:button -->\n<div class=\"wp-block-button\"><a class=\"wp-block-button__link wp-element-button\" href=\"" . esc_url($url) . "\">$label</a></div>\n<!-- /wp:button --></div>\n<!-- /wp:buttons -->\n\n";
}

function aoc_install_pages() {
    $pages = [
        'home' => [
            'title'   => 'Home',
            'excerpt' => '',
            'content' => aoc_render_pattern('home-intro'),
        ],
        'about' => [
            'title'   => 'About',
            'excerpt' => 'Age of Creativity is a research project on how creative work changes over the course of a life.',
            'content' =>
                aoc_block_h('The research question') .
                aoc_block_p('Do creators do their best work young, or does creativity take a different form later in life? The project follows the full life courses of composers, artists, writers and scientists to see when and how their work changed, and what made those changes possible.') .
                aoc_block_h('Our approach') .
                aoc_block_p('We combine historical case studies with a structured database of lives and works. Each creator is recorded with their dates, their field, the circumstances they worked under, such as patronage, employment, illness or exile, and their connections to other creators.') .
                aoc_block_p('Comparing hundreds of life courses side by side makes it possible to ask questions that a single biography cannot answer: are late-life breakthroughs more common in some fields than others, and how much do they depend on the support around the creator?') .
                aoc_block_h('Fields and period') .
                aoc_block_p('The project covers four fields, music, art, literature and science, from around 1400 to 1950. The timeline on the front page shows a selection of the people we study.'),
        ],
        'team' => [
            'title'   => 'Team',
            'excerpt' => 'The researchers behind Age of Creativity.',
            'content' => aoc_team_page_content(),
        ],
        'output' => [
            'title'   => 'Output',
            'excerpt' => 'Data, publications and reports from the project.',
            'content' => '',
        ],
        'output/database' => [
            'title'   => 'Database',
            'excerpt' => 'A structured record of creators’ lives, works and connections.',
            'content' =>
                aoc_block_p('The Age of Creativity database records when creators lived, which field they worked in, the circumstances of their working lives and who they were connected to. The timeline on the front page is drawn directly from it.') .
                aoc_block_p('The database is being built during the project. Describe here how researchers can access it, what it contains and how to cite it.') .
                aoc_block_button('Explore the timeline', home_url('/')),
        ],
        'output/publications' => [
            'title'   => 'Publications',
            'excerpt' => 'Articles, chapters and books by the project team.',
            'content' => aoc_render_pattern('publication-list'),
        ],
        'output/reports' => [
            'title'   => 'Reports',
            'excerpt' => 'Reports and working papers for download.',
            'content' => aoc_render_pattern('report-card'),
        ],
        'events' => [
            'title'   => 'Events',
            'excerpt' => 'Seminars, workshops and conferences organised by the project.',
            'content' => aoc_render_pattern('event'),
        ],
        'contact' => [
            'title'   => 'Contact',
            'excerpt' => 'Get in touch with the Age of Creativity team.',
            'content' => aoc_render_pattern('contact'),
        ],
    ];

    $ids = [];
    $order = 0;
    foreach ($pages as $path => $p) {
        $order++;
        $existing = get_page_by_path($path);
        if ($existing) {
            $ids[$path] = $existing->ID;
            continue;
        }
        $parent = 0;
        $slug   = $path;
        if (strpos($path, '/') !== false) {
            [$parent_path, $slug] = explode('/', $path, 2);
            $parent = $ids[$parent_path] ?? 0;
        }
        $content = $p['content'];
        if ($path === 'output') {
            $content = aoc_output_index_content();
        }
        $id = wp_insert_post([
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_title'   => $p['title'],
            'post_name'    => $slug,
            'post_parent'  => $parent,
            'post_excerpt' => $p['excerpt'],
            'post_content' => $content,
            'menu_order'   => $order,
        ]);
        if ($id && !is_wp_error($id)) {
            $ids[$path] = $id;
        }
    }
    return $ids;
}

function aoc_output_index_content() {
    $rows = [
        ['Database', 'output/database', 'Life courses, works and connections of the creators we study. The front-page timeline is drawn from it.'],
        ['Publications', 'output/publications', 'Articles, chapters and books by the project team.'],
        ['Reports', 'output/reports', 'Reports and working papers for download.'],
    ];
    $out = '';
    foreach ($rows as [$title, $path, $text]) {
        $out .= '<!-- wp:group {"className":"aoc-link-row","layout":{"type":"default"}} -->
<div class="wp-block-group aoc-link-row"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="' . esc_url(home_url('/' . $path . '/')) . '">' . $title . '</a></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . $text . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

';
    }
    return $out;
}

function aoc_install_navigation($pages) {
    $existing = get_posts(['post_type' => 'wp_navigation', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids']);
    if ($existing) {
        return;
    }
    $link = function ($path, $label) use ($pages) {
        if (empty($pages[$path])) {
            return '';
        }
        return sprintf(
            '<!-- wp:navigation-link {"label":"%s","type":"page","id":%d,"url":"%s","kind":"post-type"} /-->',
            esc_attr($label),
            (int) $pages[$path],
            esc_url(get_permalink($pages[$path]))
        );
    };
    $output = '';
    if (!empty($pages['output'])) {
        $output = sprintf(
            '<!-- wp:navigation-submenu {"label":"Output","type":"page","id":%d,"url":"%s","kind":"post-type"} -->%s%s%s<!-- /wp:navigation-submenu -->',
            (int) $pages['output'],
            esc_url(get_permalink($pages['output'])),
            $link('output/database', 'Database'),
            $link('output/publications', 'Publications'),
            $link('output/reports', 'Reports')
        );
    }
    $content = implode('', [
        '<!-- wp:navigation-link {"label":"Home","url":"' . esc_url(home_url('/')) . '","kind":"custom","isTopLevelLink":true} /-->',
        $link('about', 'About'),
        $link('team', 'Team'),
        $output,
        $link('events', 'Events'),
        $link('contact', 'Contact'),
    ]);
    wp_insert_post([
        'post_type'    => 'wp_navigation',
        'post_status'  => 'publish',
        'post_title'   => 'Main menu',
        'post_content' => $content,
    ]);
}
