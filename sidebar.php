<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package ProGo
 * @subpackage Ecommerce
 * @since Direct 1.0
 */
?>
<div id="secondary">
<?php
/* When we call the dynamic_sidebar() function, it'll spit out
 * the widgets for that widget area. If it instead returns false,
 * then the sidebar simply doesn't exist, so we'll hard-code in
 * some default sidebar stuff just in case.
 */
if ( ! dynamic_sidebar( 'widgets' ) ) :
global $post;
$options = get_option('progo_options');
$custom = get_post_meta($post->ID,'_progo');
$direct = $custom[0];
?>

 <div class="block secure"><h3 class="title"><span class="spacer">Easy &amp; Secure</span></h3><div class="inside">
 <img src="<?php bloginfo('template_url'); ?>/images/weaccept.gif" alt="We Accept..." />
 <span class="support">Customer Support: <?php if($options['support_email']) {
     echo '<a href="mailto:'. esc_attr($options['support']) .'">email us</a>';
 } else echo esc_html($options['support']); ?></span>
 </div></div>
 
 <div class="block share"><h3 class="title"><span class="spacer">Share</span></h3><div class="inside">
 <a name="fb_share" type="icon" href="http://www.facebook.com/sharer.php">Share</a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
 <a href="http://twitter.com/share?url=<?php echo urlencode(get_permalink($post->ID)); ?>&amp;text=Check%20Out%20This%20Great%20Product!%20" class="twitter" target="_blank">Tweet</a>
 <?php if (function_exists('sharethis_button')) { sharethis_button(); } ?>
 </div></div>
 <?php endif; // end primary widget area ?>
</div>