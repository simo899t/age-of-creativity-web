<?php
/**
 * Title: Team members
 * Slug: aoc/team-grid
 * Categories: aoc
 * Keywords: team, people, staff, portrait
 * Description: A grid of team members with photo, name, role and a short bio. Duplicate a person to add another; click a photo to replace it.
 */
if (!function_exists('aoc_person_block')) {
    require_once get_theme_file_path('inc/starter-content.php');
}
$bio = 'Two or three sentences about research interests and role in the project.';
?>
<!-- wp:group {"align":"wide","className":"aoc-people","layout":{"type":"grid","minimumColumnWidth":"13rem"}} -->
<div class="wp-block-group alignwide aoc-people">
<?php
echo aoc_person_block('Name Surname', 'Principal investigator', $bio);
echo aoc_person_block('Name Surname', 'Postdoctoral researcher', $bio);
echo aoc_person_block('Name Surname', 'PhD fellow', $bio);
?>
</div>
<!-- /wp:group -->
