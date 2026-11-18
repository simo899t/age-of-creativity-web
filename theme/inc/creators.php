<?php
/**
 * Creators: the people shown on the front-page timeline.
 *
 * Everything here is editable in wp-admin without touching code:
 *  - Creators            (post type)  name, born, died, field, short description, connections
 *  - Creators → Fields   (taxonomy)   name, colour and the field's own eras
 *  - Creators → Timeline settings     start/end year and the general periods
 *
 * Creators use the plain classic edit screen (title + a few form fields),
 * not the block editor: it's a small record, not a page, and a form is the
 * least surprising thing for someone entering data.
 */

if (!defined('ABSPATH')) {
    exit;
}

const AOC_CREATOR = 'aoc_creator';
const AOC_FIELD   = 'aoc_field';

add_action('init', function () {
    register_post_type(AOC_CREATOR, [
        'labels' => [
            'name'               => __('Creators', 'aoc'),
            'singular_name'      => __('Creator', 'aoc'),
            'add_new'            => __('Add creator', 'aoc'),
            'add_new_item'       => __('Add creator', 'aoc'),
            'edit_item'          => __('Edit creator', 'aoc'),
            'new_item'           => __('New creator', 'aoc'),
            'search_items'       => __('Search creators', 'aoc'),
            'not_found'          => __('No creators yet.', 'aoc'),
            'all_items'          => __('All creators', 'aoc'),
            'menu_name'          => __('Creators', 'aoc'),
        ],
        'description'         => __('People shown on the front-page timeline.', 'aoc'),
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'menu_position'       => 21,
        'menu_icon'           => 'dashicons-groups',
        'supports'            => ['title', 'excerpt'],
        'has_archive'         => false,
        'rewrite'             => false,
    ]);

    register_taxonomy(AOC_FIELD, AOC_CREATOR, [
        'labels' => [
            'name'          => __('Fields', 'aoc'),
            'singular_name' => __('Field', 'aoc'),
            'add_new_item'  => __('Add field', 'aoc'),
            'edit_item'     => __('Edit field', 'aoc'),
            'search_items'  => __('Search fields', 'aoc'),
            'not_found'     => __('No fields yet.', 'aoc'),
            'menu_name'     => __('Fields', 'aoc'),
        ],
        'public'            => false,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'hierarchical'      => true,
        'rewrite'           => false,
        // Our own meta box (radio buttons: a creator has exactly one field).
        'meta_box_cb'       => false,
    ]);

    foreach (['aoc_born', 'aoc_died'] as $key) {
        register_post_meta(AOC_CREATOR, $key, [
            'type'          => 'integer',
            'single'        => true,
            'show_in_rest'  => true,
            'auth_callback' => fn() => current_user_can('edit_posts'),
        ]);
    }
    register_post_meta(AOC_CREATOR, 'aoc_connections', [
        'type'          => 'array',
        'single'        => true,
        'show_in_rest'  => ['schema' => ['items' => ['type' => 'integer']]],
        'auth_callback' => fn() => current_user_can('edit_posts'),
    ]);
});

add_filter('use_block_editor_for_post_type', function ($use, $post_type) {
    return $post_type === AOC_CREATOR ? false : $use;
}, 10, 2);

add_filter('enter_title_here', function ($text, $post) {
    return $post->post_type === AOC_CREATOR ? __('Full name, e.g. Marie Curie', 'aoc') : $text;
}, 10, 2);

/* ------------------------------------------------------------------ */
/* Creator edit screen                                                 */
/* ------------------------------------------------------------------ */

add_action('add_meta_boxes_' . AOC_CREATOR, function () {
    remove_meta_box('postexcerpt', AOC_CREATOR, 'normal');
    add_meta_box('aoc_creator_details', __('Timeline details', 'aoc'), 'aoc_creator_details_box', AOC_CREATOR, 'normal', 'high');
    add_meta_box('aoc_creator_connections', __('Connected to', 'aoc'), 'aoc_creator_connections_box', AOC_CREATOR, 'normal', 'default');
});

function aoc_creator_details_box($post) {
    wp_nonce_field('aoc_creator_save', 'aoc_creator_nonce');
    $born   = get_post_meta($post->ID, 'aoc_born', true);
    $died   = get_post_meta($post->ID, 'aoc_died', true);
    $fields = get_terms(['taxonomy' => AOC_FIELD, 'hide_empty' => false]);
    $fields = is_array($fields) ? $fields : [];
    usort($fields, fn($a, $b) => [(int) get_term_meta($a->term_id, 'aoc_order', true), $a->name] <=> [(int) get_term_meta($b->term_id, 'aoc_order', true), $b->name]);
    $current = wp_get_object_terms($post->ID, AOC_FIELD, ['fields' => 'ids']);
    $current = $current ? (int) $current[0] : 0;
    ?>
    <style>
      .aoc-form { display: grid; gap: 1.1rem; max-width: 720px; padding: .4rem 0; }
      .aoc-form label { display: block; font-weight: 600; margin-bottom: .3rem; }
      .aoc-form .row { display: flex; gap: 1.5rem; flex-wrap: wrap; }
      .aoc-form input[type=number] { width: 8rem; }
      .aoc-form textarea { width: 100%; min-height: 5.5rem; }
      .aoc-form .hint { color: #646970; margin: .25rem 0 0; }
      .aoc-form fieldset label { display: inline-flex; align-items: center; gap: .4rem; font-weight: 400; margin-right: 1.2rem; }
      .aoc-swatch { display: inline-block; width: .8rem; height: .8rem; border-radius: 50%; }
    </style>
    <div class="aoc-form">
      <div class="row">
        <div>
          <label for="aoc_born"><?php esc_html_e('Year born', 'aoc'); ?></label>
          <input type="number" id="aoc_born" name="aoc_born" value="<?php echo esc_attr($born); ?>" min="0" max="2100" required>
        </div>
        <div>
          <label for="aoc_died"><?php esc_html_e('Year died', 'aoc'); ?></label>
          <input type="number" id="aoc_died" name="aoc_died" value="<?php echo esc_attr($died); ?>" min="0" max="2100">
          <p class="hint"><?php esc_html_e('Leave empty if still alive.', 'aoc'); ?></p>
        </div>
      </div>

      <fieldset>
        <legend style="font-weight:600;margin-bottom:.4rem"><?php esc_html_e('Field', 'aoc'); ?></legend>
        <?php if (!$fields) : ?>
          <p class="hint"><?php
            printf(
                /* translators: %s: link to the Fields screen */
                esc_html__('No fields yet. Add one under %s first.', 'aoc'),
                '<a href="' . esc_url(admin_url('edit-tags.php?taxonomy=' . AOC_FIELD . '&post_type=' . AOC_CREATOR)) . '">' . esc_html__('Creators → Fields', 'aoc') . '</a>'
            ); ?></p>
        <?php endif; ?>
        <?php foreach ($fields as $term) : ?>
          <label>
            <input type="radio" name="aoc_field" value="<?php echo (int) $term->term_id; ?>" <?php checked($current, $term->term_id); ?>>
            <span class="aoc-swatch" style="background:<?php echo esc_attr(aoc_field_color($term->term_id)); ?>"></span>
            <?php echo esc_html($term->name); ?>
          </label>
        <?php endforeach; ?>
      </fieldset>

      <div>
        <label for="excerpt"><?php esc_html_e('Short description', 'aoc'); ?></label>
        <textarea id="excerpt" name="excerpt" maxlength="300"><?php echo esc_textarea($post->post_excerpt); ?></textarea>
        <p class="hint"><?php esc_html_e('One or two sentences. Shown when a visitor points at this person on the timeline.', 'aoc'); ?></p>
      </div>
    </div>
    <?php
}

function aoc_creator_connections_box($post) {
    $selected = array_map('intval', (array) get_post_meta($post->ID, 'aoc_connections', true));
    $others = get_posts([
        'post_type'      => AOC_CREATOR,
        'post_status'    => ['publish', 'draft', 'pending', 'private'],
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'exclude'        => [$post->ID],
    ]);
    ?>
    <p class="description" style="margin-top:0"><?php esc_html_e('Tick the people this creator influenced or was influenced by. Lines are drawn between them on the timeline. A connection only needs ticking on one of the two people.', 'aoc'); ?></p>
    <input type="search" class="aoc-conn-filter" placeholder="<?php esc_attr_e('Filter names…', 'aoc'); ?>" style="width:100%;max-width:320px;margin:.3rem 0 .6rem">
    <div class="aoc-conn-list" style="columns: 15rem; max-height: 22rem; overflow: auto; border: 1px solid #dcdcde; padding: .6rem .8rem; background: #fff;">
      <?php foreach ($others as $other) : ?>
        <label style="display:block;break-inside:avoid;padding:.15rem 0">
          <input type="checkbox" name="aoc_connections[]" value="<?php echo (int) $other->ID; ?>" <?php checked(in_array($other->ID, $selected, true)); ?>>
          <?php echo esc_html($other->post_title); ?>
        </label>
      <?php endforeach; ?>
    </div>
    <script>
      document.querySelector('.aoc-conn-filter').addEventListener('input', function (e) {
        var q = e.target.value.toLowerCase();
        document.querySelectorAll('.aoc-conn-list label').forEach(function (l) {
          l.style.display = l.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : 'block';
        });
      });
    </script>
    <?php
}

add_action('save_post_' . AOC_CREATOR, function ($post_id) {
    if (!isset($_POST['aoc_creator_nonce']) || !wp_verify_nonce(sanitize_key($_POST['aoc_creator_nonce']), 'aoc_creator_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    foreach (['aoc_born', 'aoc_died'] as $key) {
        $raw = isset($_POST[$key]) ? trim(wp_unslash($_POST[$key])) : '';
        if ($raw === '') {
            delete_post_meta($post_id, $key);
        } else {
            update_post_meta($post_id, $key, (int) $raw);
        }
    }

    $field = isset($_POST['aoc_field']) ? (int) $_POST['aoc_field'] : 0;
    wp_set_object_terms($post_id, $field ? [$field] : [], AOC_FIELD);

    $connections = isset($_POST['aoc_connections']) ? array_map('intval', (array) $_POST['aoc_connections']) : [];
    update_post_meta($post_id, 'aoc_connections', array_values(array_filter($connections)));
});

/* List screen: show years next to the name, sorted chronologically. */
add_filter('manage_' . AOC_CREATOR . '_posts_columns', function ($cols) {
    $out = [];
    foreach ($cols as $key => $label) {
        $out[$key] = $label;
        if ($key === 'title') {
            $out['aoc_years'] = __('Lived', 'aoc');
        }
    }
    unset($out['date']);
    return $out;
});

add_action('manage_' . AOC_CREATOR . '_posts_custom_column', function ($col, $post_id) {
    if ($col === 'aoc_years') {
        $born = get_post_meta($post_id, 'aoc_born', true);
        $died = get_post_meta($post_id, 'aoc_died', true);
        echo esc_html(($born ?: '?') . '–' . ($died ?: ''));
    }
}, 10, 2);

add_filter('manage_edit-' . AOC_CREATOR . '_sortable_columns', function ($cols) {
    $cols['aoc_years'] = 'aoc_born';
    return $cols;
});

add_action('pre_get_posts', function ($q) {
    if (!is_admin() || !$q->is_main_query() || $q->get('post_type') !== AOC_CREATOR) {
        return;
    }
    if (!$q->get('orderby') || $q->get('orderby') === 'aoc_born') {
        $q->set('meta_key', 'aoc_born');
        $q->set('orderby', 'meta_value_num');
        $q->set('order', $q->get('order') ?: 'ASC');
    }
});

/* ------------------------------------------------------------------ */
/* Fields: colour + eras                                               */
/* ------------------------------------------------------------------ */

function aoc_field_color($term_id) {
    $c = get_term_meta($term_id, 'aoc_color', true);
    return sanitize_hex_color($c) ?: '#6b665e';
}

/** "Baroque, 1600, 1750" per line → [['label'=>..,'start'=>..,'end'=>..], ...] */
function aoc_parse_eras($text) {
    $eras = [];
    foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $line) {
        $parts = array_map('trim', explode(',', $line));
        if (count($parts) < 3) {
            continue;
        }
        $end   = (int) array_pop($parts);
        $start = (int) array_pop($parts);
        $label = implode(', ', $parts);
        if ($label !== '' && $end > $start) {
            $eras[] = ['label' => $label, 'start' => $start, 'end' => $end];
        }
    }
    return $eras;
}

function aoc_format_eras(array $eras) {
    return implode("\n", array_map(fn($e) => $e['label'] . ', ' . $e['start'] . ', ' . $e['end'], $eras));
}

function aoc_field_form_rows($term = null) {
    $color = $term ? aoc_field_color($term->term_id) : '#4c5fd5';
    $eras  = $term ? (string) get_term_meta($term->term_id, 'aoc_eras', true) : '';
    $order = $term ? (int) get_term_meta($term->term_id, 'aoc_order', true) : 0;
    $rows = [
        ['aoc_color', __('Colour', 'aoc'), '<input type="color" name="aoc_color" id="aoc_color" value="' . esc_attr($color) . '">', __('Used for this field\'s dots and lines on the timeline.', 'aoc')],
        ['aoc_order', __('Order', 'aoc'), '<input type="number" name="aoc_order" id="aoc_order" value="' . esc_attr($order) . '" style="width:6rem">', __('Fields are listed from lowest to highest number.', 'aoc')],
        ['aoc_eras', __('Eras', 'aoc'), '<textarea name="aoc_eras" id="aoc_eras" rows="6" style="width:100%;font-family:monospace">' . esc_textarea($eras) . '</textarea>', __('One era per line: name, start year, end year. Example: Baroque, 1600, 1750. Shown under the timeline when this field is selected.', 'aoc')],
    ];
    return $rows;
}

add_action(AOC_FIELD . '_add_form_fields', function () {
    foreach (aoc_field_form_rows() as [$id, $label, $input, $hint]) {
        echo '<div class="form-field"><label for="' . esc_attr($id) . '">' . esc_html($label) . '</label>' . $input . '<p>' . esc_html($hint) . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- $input escaped above
    }
});

add_action(AOC_FIELD . '_edit_form_fields', function ($term) {
    foreach (aoc_field_form_rows($term) as [$id, $label, $input, $hint]) {
        echo '<tr class="form-field"><th scope="row"><label for="' . esc_attr($id) . '">' . esc_html($label) . '</label></th><td>' . $input . '<p class="description">' . esc_html($hint) . '</p></td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput
    }
});

$aoc_save_field = function ($term_id) {
    if (!current_user_can('manage_categories')) {
        return;
    }
    if (isset($_POST['aoc_color'])) {
        update_term_meta($term_id, 'aoc_color', sanitize_hex_color(wp_unslash($_POST['aoc_color'])) ?: '#6b665e');
    }
    if (isset($_POST['aoc_order'])) {
        update_term_meta($term_id, 'aoc_order', (int) $_POST['aoc_order']);
    }
    if (isset($_POST['aoc_eras'])) {
        // Normalise on save so typos (missing years etc.) are dropped visibly.
        update_term_meta($term_id, 'aoc_eras', aoc_format_eras(aoc_parse_eras(sanitize_textarea_field(wp_unslash($_POST['aoc_eras'])))));
    }
};
add_action('created_' . AOC_FIELD, $aoc_save_field);
add_action('edited_' . AOC_FIELD, $aoc_save_field);

add_filter('manage_edit-' . AOC_FIELD . '_columns', function ($cols) {
    unset($cols['description'], $cols['slug']);
    $cols = array_slice($cols, 0, 1, true) + ['aoc_color' => ''] + array_slice($cols, 1, null, true);
    return $cols;
});
add_filter('manage_' . AOC_FIELD . '_custom_column', function ($out, $col, $term_id) {
    if ($col === 'aoc_color') {
        return '<span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:' . esc_attr(aoc_field_color($term_id)) . '"></span>';
    }
    return $out;
}, 10, 3);

/* ------------------------------------------------------------------ */
/* Timeline settings page                                              */
/* ------------------------------------------------------------------ */

function aoc_timeline_settings() {
    $defaults = [
        'start'   => 1400,
        'end'     => 1950,
        'periods' => "Renaissance, 1400, 1600\nEarly Modern, 1600, 1750\nEnlightenment, 1750, 1850\nModern, 1850, 1950",
    ];
    return wp_parse_args((array) get_option('aoc_timeline', []), $defaults);
}

add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=' . AOC_CREATOR,
        __('Timeline settings', 'aoc'),
        __('Timeline settings', 'aoc'),
        'manage_options',
        'aoc-timeline',
        'aoc_timeline_settings_page'
    );
});

add_action('admin_init', function () {
    register_setting('aoc_timeline', 'aoc_timeline', [
        'type'              => 'array',
        'sanitize_callback' => function ($in) {
            $start = isset($in['start']) ? (int) $in['start'] : 1400;
            $end   = isset($in['end']) ? (int) $in['end'] : 1950;
            if ($end <= $start) {
                $end = $start + 100;
            }
            return [
                'start'   => $start,
                'end'     => $end,
                'periods' => aoc_format_eras(aoc_parse_eras(sanitize_textarea_field($in['periods'] ?? ''))),
            ];
        },
    ]);
});

function aoc_timeline_settings_page() {
    $s = aoc_timeline_settings();
    ?>
    <div class="wrap">
      <h1><?php esc_html_e('Timeline settings', 'aoc'); ?></h1>
      <p><?php esc_html_e('Controls the interactive timeline on the front page. People are added under Creators, colours and field-specific eras under Fields.', 'aoc'); ?></p>
      <form method="post" action="options.php">
        <?php settings_fields('aoc_timeline'); ?>
        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><label for="aoc_start"><?php esc_html_e('Timeline starts', 'aoc'); ?></label></th>
            <td><input type="number" id="aoc_start" name="aoc_timeline[start]" value="<?php echo esc_attr($s['start']); ?>" class="small-text"></td>
          </tr>
          <tr>
            <th scope="row"><label for="aoc_end"><?php esc_html_e('Timeline ends', 'aoc'); ?></label></th>
            <td><input type="number" id="aoc_end" name="aoc_timeline[end]" value="<?php echo esc_attr($s['end']); ?>" class="small-text"></td>
          </tr>
          <tr>
            <th scope="row"><label for="aoc_periods"><?php esc_html_e('General periods', 'aoc'); ?></label></th>
            <td>
              <textarea id="aoc_periods" name="aoc_timeline[periods]" rows="6" class="large-text code"><?php echo esc_textarea($s['periods']); ?></textarea>
              <p class="description"><?php esc_html_e('Shown under the timeline when “All fields” is selected. One per line: name, start year, end year.', 'aoc'); ?></p>
            </td>
          </tr>
        </table>
        <?php submit_button(); ?>
      </form>
    </div>
    <?php
}

/* ------------------------------------------------------------------ */
/* Data for the timeline block                                         */
/* ------------------------------------------------------------------ */

function aoc_timeline_data() {
    $settings = aoc_timeline_settings();

    $terms = get_terms(['taxonomy' => AOC_FIELD, 'hide_empty' => false]);
    $fields = [];
    foreach (is_array($terms) ? $terms : [] as $t) {
        $fields[] = [
            'id'    => $t->term_id,
            'slug'  => $t->slug,
            'name'  => $t->name,
            'color' => aoc_field_color($t->term_id),
            'order' => (int) get_term_meta($t->term_id, 'aoc_order', true),
            'eras'  => aoc_parse_eras(get_term_meta($t->term_id, 'aoc_eras', true)),
        ];
    }
    usort($fields, fn($a, $b) => [$a['order'], $a['name']] <=> [$b['order'], $b['name']]);

    $posts = get_posts([
        'post_type'      => AOC_CREATOR,
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'no_found_rows'  => true,
    ]);
    $people = [];
    foreach ($posts as $p) {
        $born = get_post_meta($p->ID, 'aoc_born', true);
        if ($born === '') {
            continue;
        }
        $terms = wp_get_object_terms($p->ID, AOC_FIELD, ['fields' => 'slugs']);
        $died  = get_post_meta($p->ID, 'aoc_died', true);
        $people[] = [
            'id'          => $p->ID,
            'name'        => html_entity_decode(get_the_title($p), ENT_QUOTES),
            'born'        => (int) $born,
            'died'        => $died === '' ? null : (int) $died,
            'field'       => $terms && !is_wp_error($terms) ? $terms[0] : '',
            'description' => $p->post_excerpt,
            'connections' => array_values(array_map('intval', (array) get_post_meta($p->ID, 'aoc_connections', true))),
        ];
    }
    usort($people, fn($a, $b) => $a['born'] <=> $b['born']);

    return [
        'start'   => (int) $settings['start'],
        'end'     => (int) $settings['end'],
        'periods' => aoc_parse_eras($settings['periods']),
        'fields'  => $fields,
        'people'  => $people,
    ];
}
