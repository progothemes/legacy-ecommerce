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
?>
<div class="block widget_wpsc_shopping_cart">
    <h3 class="title"><span class="spacer">Shopping Cart</span></h3>
    <div class="inside">
        <div class="shopping-cart-wrapper" id="sliding_cart">
        <?php
			if ( function_exists('wpsc_get_template_file_path') ) {
            	include( wpsc_get_template_file_path( 'wpsc-cart_widget.php' ) );
			} elseif ( current_user_can( 'activate_plugins' ) ) {
				echo '<p>Install &amp; Activate the WP e-Commerce Plugin to enable your Store &amp; Shopping Cart.</p>';
			} else {
				echo '<p>Coming soon...</p>';
			}
        ?>
        </div>
    </div>
</div>
<div class="block share">
    <h3 class="title"><span class="spacer">Share</span></h3>
    <div class="inside">
        <?php
        if (function_exists('sharethis_button')) {
			sharethis_button();
		} else { ?>
        <a name="fb_share" type="icon" href="http://www.facebook.com/sharer.php">Share</a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
        <a href="http://twitter.com/share?url=<?php echo urlencode(get_permalink($post->ID)); ?>&amp;text=Check%20Out%20This%20Great%20Product!%20" class="twitter" target="_blank">Tweet</a>
		<?php
		}
        ?>
    </div>
</div>
<?php
endif; // end primary widget area ?>
</div>
</div>