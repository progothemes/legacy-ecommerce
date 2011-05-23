<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package ProGo
 * @subpackage Ecommerce
 * @since Direct 1.0
 */
?>
<div class="grid_4">
<div id="secondary">
<?php
/* When we call the dynamic_sidebar() function, it'll spit out
 * the widgets for that widget area. If it instead returns false,
 * then the sidebar simply doesn't exist, so we'll hard-code in
 * some default sidebar stuff just in case.
 */
$sidebar = '';
if ( is_page() ) {
	global $post;
	$custom = get_post_meta($post->ID,'_progo_sidebar');
	$sidebar = $custom[0];
}
if ( $sidebar == '' ) {
	$sidebar = 'main';
}
if ( ! dynamic_sidebar( $sidebar ) ) :
// do SHOPPING CART widget ?

endif; // end primary widget area ?>
</div>
</div>