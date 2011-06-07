<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content
 * after.  Calls sidebar-footer.php for bottom widgets.
 *
 * @package ProGo
 * @subpackage Ecommerce
 * @since Ecommerce 1.0
 */
?>
<div id="fbar">
<?php if(!dynamic_sidebar('fbar')) {
	wp_nav_menu( array( 'container_class' => 'fblock widget_nav_menu', 'theme_location' => 'mainmenu' ) );
} ?>
</div>
	</div><!-- #page -->
	<div id="ftr" class="container_12">
    <div class="grid_8<?php
$fmenu = wp_nav_menu( array( 'container' => false, 'theme_location' => 'ftrlnx', 'echo' => 0, 'fallback_cb' => 'progo_nomenu_cb' ) );
if( strpos( $fmenu, '</li>' ) > 0 ) {
	$fmenu = str_replace('</li>','&nbsp;&nbsp;|&nbsp;&nbsp;</li>',substr($fmenu,0,strrpos($fmenu,'</li>'))) . "</li>\n</ul>";
	echo '">'. $fmenu .'<br />';
} else {
	echo ' nom">';	
}
$options = get_option('progo_options');
echo wp_kses($options['copyright'],array());
?>
</div>
<div class="grid_4 right">Powered by <a href="http://www.wordpress.org" target="_blank">WordPress</a>. Designed by <a href="http://www.progo.com/" title="Performance WordPress Themes" target="_blank"><img src="<?php bloginfo('template_url'); ?>/images/logo_admin.png" alt="ProGo" /></a></div>
</div><!-- #ftr -->
</div><!-- #wrap -->
</div>
<?php
	/* Always have wp_footer() just before the closing </body>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to reference JavaScript files.
	 */

	wp_footer();
?>
</body>
</html>
