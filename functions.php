<?php
/**
 * @package ProGo
 * @subpackage Ecommerce
 * @since Ecommerce 1.0
 *
 * Defines all the functions, actions, filters, widgets, etc., for ProGo Themes' Ecommerce theme.
 *
 * Some actions for Child Themes to hook in to are:
 * progo_frontend_scripts, progo_frontend_styles, progo_direct_after_arrow (called on directresponse.php page)
 *
 * Some overwriteable functions ( wrapped by "if(!function_exists(..." ) are:
 * progo_sitelogo, progo_posted_on, progo_posted_in, progo_productimage, progo_prepare_transaction_results,
 * progo_admin_menu_cleanup, progo_custom_login_logo, progo_custom_login_url, progo_metabox_cleanup, progo_colorschemes ...
 *
 * Most Action / Filters hooks are set in the progo_setup function, below. overwriting that could cause quite a few things to go wrong.
 */

$content_width = 594;

/** Tell WordPress to run progo_setup() when the 'after_setup_theme' hook is run. */
add_action( 'after_setup_theme', 'progo_setup' );

if ( ! function_exists( 'progo_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @uses register_nav_menus() To add support for navigation menus.
 * @uses add_custom_background() To add support for a custom background.
 * @uses add_theme_support( 'post-thumbnails' ) To add support for post thumbnails.
 *
 * @since Ecommerce 1.0
 */
function progo_setup() {
	// This theme styles the visual editor with editor-style.css to match the theme style
	add_editor_style( 'css/editor-style.css' );
	
	// This theme uses wp_nav_menu() in two locations
	register_nav_menus( array(
		'mainmenu' => 'Main Menu',
		'ftrlnx' => 'Footer Links'
	) );
	
	// Add support for custom backgrounds
	add_custom_background();
	
	// Add support for post thumbnails
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'large', 596, 397, true );
	add_image_size( 'prodL', 290, 290, true );
	add_image_size( 'prod3', 190, 190, true );
	add_image_size( 'thm', 70, 70, true );
	
	// Add custom actions
	add_action( 'admin_init', 'progo_admin_init' );
	add_action( 'widgets_init', 'progo_ecommerce_widgets' );
	add_action( 'admin_menu', 'progo_admin_menu_cleanup' );
	add_action( 'login_head', 'progo_custom_login_logo' );
	add_action( 'login_headerurl', 'progo_custom_login_url' );
	add_action( 'save_post', 'progo_save_meta' );
	add_action('wp_print_scripts', 'progo_add_scripts');
	add_action('wp_print_styles', 'progo_add_styles');
	add_action( 'wp_before_admin_bar_render', 'progo_admin_bar_render' );
	
	// add custom filters
	add_filter( 'body_class', 'progo_bodyclasses' );
	add_filter( 'wp_nav_menu_objects', 'progo_menuclasses' );
	add_filter( 'site_transient_update_themes', 'progo_update_check' );
	add_filter( 'wpsc_pre_transaction_results', 'progo_prepare_transaction_results' );
	add_filter( 'wp_mail_content_type', 'progo_mail_content_type' );
	add_filter('custom_menu_order', 'progo_admin_menu_order');
	add_filter('menu_order', 'progo_admin_menu_order');
	
	if ( is_admin() ) {
		add_action( 'admin_notices', 'progo_admin_notices' );
	} else {
		// brick site if theme is not activated
		if ( get_option( 'progo_ecommerce_apiauth' ) != 100 ) {
			add_action( 'template_redirect', 'progo_to_twentyten' );
		}
	}
}
endif;

/********* Front-End Functions *********/

if ( ! function_exists( 'progo_sitelogo' ) ):
/**
 * prints out the HTML for the #logo area in the header of the front-end of the site
 * wrapped so child themes can overwrite if desired
 * @since Ecommerce 1.0.46
 */
function progo_sitelogo() {
	$options = get_option( 'progo_options' );
	$progo_logo = $options['logo'];
	$upload_dir = wp_upload_dir();
	$dir = trailingslashit($upload_dir['baseurl']);
	$imagepath = $dir . $progo_logo;
	if($progo_logo) {
		echo '<table id="logo"><tr><td><a href="'. get_bloginfo('url') .'"><img src="'. esc_attr( $imagepath ) .'" alt="'. esc_attr( get_bloginfo( 'name' ) ) .'" /></a></td></tr></table>';
	} else {
		echo '<a href="'. get_bloginfo('url') .'" id="logo">'. esc_html( get_bloginfo( 'name' ) ) .'<span class="g"></span></a>';
	}
}
endif;
if ( ! function_exists( 'progo_posted_on' ) ):
/**
 * Prints HTML with meta information for the current post—date/time and author.
 * @since ProGo Ecommerce 1.0
 */
function progo_posted_on() {
	printf( __( '<span class="meta-sep">Posted by</span> %1$s <span class="%2$s">on</span> %3$s', 'progo' ),
		sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
			get_author_posts_url( get_the_author_meta( 'ID' ) ),
			sprintf( esc_attr__( 'View all posts by %s', 'progo' ), get_the_author() ),
			get_the_author()
		),
		'meta-prep meta-prep-author',
		sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
			get_permalink(),
			esc_attr( get_the_time() ),
			get_the_date()
		)
	);
	edit_post_link( __( 'Edit', 'progo' ), '<span class="meta-sep"> : </span> <span class="edit-link">', '</span>' );
}
endif;
if ( ! function_exists( 'progo_posted_in' ) ):
/**
 * Prints HTML with meta information for the current post (category, tags and permalink).
 * @since ProGo Ecommerce 1.0
 */
function progo_posted_in() {
	/* Retrieves tag list of current post, separated by commas.
	$tag_list = get_the_tag_list( '', ', ' );
	*/
	echo 'Categories : '. get_the_category_list( ', ' );
}
endif;
if ( ! function_exists( 'progo_productimage' ) ):
/**
 * echoes html for product image, or default product image if there isnt one
 * @since Ecommerce 1.0
 */
function progo_productimage($pID) {
	if(($pID==0) || has_post_thumbnail( $pID ) == false) {
		echo '<img src="'. get_bloginfo('template_url') .'/images/productimage.gif" alt="Product Image" />';
	} else {
		echo get_the_post_thumbnail( $pID, 'original');
	}
}
endif;
if ( ! function_exists( 'progo_prepare_transaction_results' ) ):
/**
 * filter for wpsc_pre_transaction_results
 * @since Ecommerce 1.0
 */
function progo_prepare_transaction_results() {
	global $purchase_log;
	$options = get_option( 'progo_options' );
	$purchase_log['find_us'] = '<table><tr class="firstrow"><td>Our Company Info</td></tr><tr><td>'. esc_html( $options['companyinfo'] ) .'</td></tr></table>';
}
endif;
if ( ! function_exists( 'progo_summary' ) ):
/**
 * chops off (product) text either @ <!-- more --> or last space before 152 characters
 * @since Ecommerce 1.0
 */
function progo_summary( $morelink, $limit = 150 ) {
	global $post;
	$content = $post->post_content;
	$lbrat = strpos( $content, "\n" );
	if( $lbrat > 0 && $lbrat < $limit ) {
		$content = substr( $content, 0, $lbrat );
	} else {
		$content = substr( $content, 0, strrpos( substr( $content, 0, $limit ), ' ' ) ) ."...";
	}
	if( $morelink != false ) {
		$content .= "\n<a href='". wpsc_the_product_permalink() ."' class='more-link'>$morelink</a>";
	}
	echo wpautop($content);
}
endif;
if ( ! function_exists( 'progo_comments' ) ):
/**
 * walker function for comment display
 * @since Ecommerce 1.0.9
 */
function progo_comments($comment, $args, $depth) {	
	$GLOBALS['comment'] = $comment;
	extract($args, EXTR_SKIP);
	
	if ( 'div' == $args['style'] ) {
		$tag = 'div';
		$add_below = 'comment';
	} else {
		$tag = 'li';
		$add_below = 'div-comment';
	}
	?>
	<<?php echo $tag ?> <?php comment_class(empty( $args['has_children'] ) ? '' : 'parent') ?> id="comment-<?php comment_ID() ?>">
	<?php if ( 'div' != $args['style'] ) : ?>
	<div id="div-comment-<?php comment_ID() ?>" class="comment-body">
	<?php endif; ?>
	<div class="comment-meta"><div class="comment-author vcard">
	<?php echo get_comment_author_link() ?>
	</div>
	<div class="meta"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>">
		<?php
			/* translators: 1: date, 2: time */
			printf( __('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?></a><?php edit_comment_link(__('(Edit)'),'&nbsp;&nbsp;','' );
		?>
	</div>
    </div>
	<?php if ($args['avatar_size'] != 0) echo get_avatar( $comment, $args['avatar_size'] ); ?>
	<?php if ($comment->comment_approved == '0') : ?>
	<em class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.') ?></em>
	<br />
	<?php endif; ?>
	<?php comment_text() ?>
	
	<div class="reply">
	<?php comment_reply_link(array_merge( $args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
	</div>
	<?php if ( 'div' != $args['style'] ) : ?>
	</div>
	<?php endif; ?>
	<?php
}
endif;
if ( ! function_exists( 'progo_product_image' ) ):
/**
 * wpsc_the_product_image does not actually care
 * about the $width & $height args, at least as of 3.8.3,
 * so this fixes that
 *
 * @param image (thumbnail) width
 * @param image (thumbnail) height
 * @param product (post) id
 *
 * @since Ecommerce 1.1.0
 */
function progo_product_image( $width='', $height='', $product_id='' ) {
	if ( empty( $product_id ) )
		$product_id = get_the_ID();

	$imagesize = ( ( $width == '' ) && ( $height == '' ) ) ? 'large' : array( $width, $height );

	$product = get_post( $product_id );

	if ( $product->post_parent > 0 )
		$product_id = $product->post_parent;

	$attached_images = (array)get_posts( array(
				'post_type' => 'attachment',
				'numberposts' => 1,
				'post_status' => null,
				'post_parent' => $product_id,
				'orderby' => 'menu_order',
				'order' => 'ASC'
			) );


	$post_thumbnail_id = get_post_thumbnail_id( $product_id );

	$src = wp_get_attachment_image_src( $post_thumbnail_id, $imagesize );

	if ( ! empty( $src ) && is_string( $src[0] ) ) {
		$src = $src[0];
	} elseif ( ! empty( $attached_images ) ) {
		$attached_image = wp_get_attachment_image_src( $attached_images[0]->ID, $imagesize );
		$src = $attached_image[0];
	} else {
		$src = false;
	}
	
	if ( is_ssl() && ! empty( $src ) )
		$src = str_replace( 'http://', 'https://', $src );
	$src = apply_filters( 'wpsc_product_image', $src );
	
	return $src;
}
endif;
/********* Back-End Functions *********/
if ( ! function_exists( 'progo_admin_menu_cleanup' ) ):
/**
 * hooked to 'admin_menu' by add_action in progo_setup()
 * @since Ecommerce 1.0
 */
function progo_admin_menu_cleanup() {
	global $menu;
	global $submenu;
	
	// add Theme Options and Homepage Slides pages under APPEARANCE
	add_theme_page( 'Homepage Slides', 'Homepage Slides', 'edit_theme_options', 'progo_home_slides', 'progo_home_slides_page' );
	add_theme_page( 'Theme Options', 'Theme Options', 'edit_theme_options', 'progo_admin', 'progo_admin_page' );
	// and reorder that APPEARANCE submenu
	$sub = $submenu['themes.php'];
	$sub1 = array_shift($sub);
	rsort($sub);
	$sub1[0] = 'Change Theme';
	$sub[] = $sub1;
	$submenu['themes.php'] = $sub;
	
//	wp_die('<pre>'. print_r($menu,true) .'</pre>');
}
endif;
if ( ! function_exists( 'progo_admin_menu_order' ) ):
function progo_admin_menu_order($menu_ord) {
    if (!$menu_ord) return true;
    return array(
     'index.php', // this represents the dashboard link
	 'separator1',
     'edit.php?post_type=wpsc-product', // this is a custom post type menu
     'edit.php?post_type=page', // this is the default page menu
     'edit.php', // this is the default POST admin menu
     'upload.php', // this is the default POST admin menu
     'edit-comments.php', // this is the default POST admin menu
     'link-manager.php' // this is the default POST admin menu
 );
}
endif;
if ( ! function_exists( 'progo_admin_menu_finder' ) ):
/**
 * helper function to find the $key for the menu item with given $slug
 * @since Ecommerce 1.1.0
 */
function progo_admin_menu_finder($menu, $slug) {
	$id = 0;
	foreach ( $menu as $k => $v ) {
		if( $v[2] == $slug ) {
			$id = $k;
		}
	}
	return $id;
}
endif;
if ( ! function_exists( 'progo_admin_page' ) ):
/**
 * ProGo Themes' Ecommerce Admin Page function
 * switch statement creates Pages for Installation, Shipping, Payment, Products, Appearance
 * from admin_menu_cleanup()
 
 * @since Ecommerce 1.0.3
 */
function progo_admin_page() {
	//must check that the user has the required capability 
	if ( current_user_can('edit_theme_options') == false) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	} ?>
<script type="text/javascript">/* <![CDATA[ */
var wpsc_adminL10n = {
	unsaved_changes_detected: "Unsaved changes have been detected. Click OK to lose these changes and continue.",
	dragndrop_set: "false"
};
try{convertEntities(wpsc_adminL10n);}catch(e){};
/* ]]> */
</script>
    <?php
	$thispage = $_GET['page'];
	switch($thispage) {
		case "progo_admin":
	?>
	<div class="wrap">
    <div class="icon32" id="icon-themes"><br /></div>
    <h2>ProGo Ecommerce Theme Options</h2>
	<form action="options.php" method="post" enctype="multipart/form-data"><?php
		settings_fields( 'progo_options' );
		do_settings_sections( 'progo_api' );
		?>
        <p class="submit"><input type="submit" value="Save Changes" class="button-primary" /></p>
        <?php
		do_settings_sections( 'progo_theme' );
		do_settings_sections( 'progo_info' );
		do_settings_sections( 'progo_hometop' );
		?>
        <p class="submit"><input type="submit" value="Save Changes" class="button-primary" /></p>
        <h3>WP e-Commerce</h3>
		<p>Your ProGo <em>Ecommerce</em> Theme works hand-in-hand with the <strong>WP e-Commerce</strong> Plugin.</p>
		<?php
	// check for wp-e-commerce installed..
	$plugs = get_plugins();
	if( isset( $plugs['wp-e-commerce/wp-shopping-cart.php'] ) == false ) {
		$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url( 'update.php?action=install-plugin&amp;plugin=wp-e-commerce', 'install-plugin_wp-e-commerce' ) : 'plugin-install.php';
		echo '<p><a href="'. esc_url( $lnk ) .'" class="button-primary">Install WP e-Commerce now &raquo;</a></p>';
	} else {
		if ( function_exists('wpsc_admin_pages')) {
			?><table class="form-table">
            <tr valign="top">
            <th scope="row">Store Settings</th>
            <td><?php progo_ecommerce_reccheck( true ); ?></td></tr>
            </table><?php
        } else {
			$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url('plugins.php?action=activate&amp;plugin=wp-e-commerce/wp-shopping-cart.php&amp;plugin_status=all&amp;paged=1', 'activate-plugin_wp-e-commerce/wp-shopping-cart.php') : 'plugins.php';
			echo '<p><a href="'. esc_url($lnk) .'" class="button-primary">Activate WP e-Commerce &raquo;</a><p>';
			$goon = false;
		}
	}
		?>
		<p><br /></p>
		</form>
        <h3>Additional Options</h3>
        <table class="form-table">
        <?php
		$addl = array(
			'Homepage Slides' => array(
				'url' => 'themes.php?page=progo_home_slides',
				'btn' => 'Manage Homepage Slides',
				'desc' => ''
			),
			'Background' => array(
				'url' => 'themes.php?page=custom-background',
				'btn' => 'Customize Your Background',
				'desc' => 'Change the underlying color, or upload your own custom background image.'
			),
			'Menus' => array(
				'url' => 'nav-menus.php',
				'btn' => 'Manage Menu Links',
				'desc' => 'Control the links in the Header &amp; Footer area of your site.'
			),
			'Widgets' => array(
				'url' => 'widgets.php',
				'btn' => 'Manage Widgets',
				'desc' => 'Customize what appears in the right column on various areas of your site.'
			)
		);
		foreach ( $addl as $k => $v ) {
			echo '<tr><th scope="row">'. wp_kses($k,array()) .'</th><td><a href="'. esc_url($v['url']) .'" class="button">'. wp_kses($v['btn'],array()) .' &raquo;</a> <span class="description">'. wp_kses($v['desc'],array()) .'</span></td></tr>';
		} ?>
        </table><p><br /></p>
        <h3>Recommended Plugins</h3>
                <?php if ( function_exists( 'alex_recommends_widget' ) ) {
					alex_recommends_widget();
				} else { ?>
                    <p>The following plugins can help improve various aspects of your WordPress / ProGo Themes site:</p>
                    <ul style="list-style:outside; padding: 0 1em">
                    <?php
					$pRec = array();
					$pRec[] = array('name'=>'All in One SEO Pack','stub'=>'all-in-one-seo-pack','desc'=>'Out-of-the-box SEO. Easily control your pages\' keywords / meta description, and more');
					$pRec[] = array('name'=>'ShareThis','stub'=>'share-this','desc'=>'Let your visitors share your Products with others, posting to Facebook/Twitter/social bookmarking sites, and emailing to friends');
					$pRec[] = array('name'=>'Ultimate Google Analytics','stub'=>'ultimate-google-analytics','desc'=>'Add Google Analytics to your site, with options to track external links, mailto\'s, and downloads');
					$pRec[] = array('name'=>'Google XML Sitemaps','stub'=>'google-sitemap-generator','desc'=>'Generate an XML sitemap to help search engines like Google, Yahoo, Bing and Ask.com better index your site');
					$pRec[] = array('name'=>'WB DB Backup','stub'=>'wp-db-backup','desc'=>'On-demand backup of your WordPress database');
					$pRec[] = array('name'=>'Duplicate Post','stub'=>'duplicate-post','desc'=>'Add functionality to Save Page As...');
					$pRec[] = array('name'=>'Gold Cart for WP e-Commerce','stub'=>'','desc'=>'Extend your WP e-Commerce store with additional payment gateways and multiple product image');
					
					foreach( $pRec as $plug ) {
						echo '<li>';
						if ( $plug['name'] == 'Gold Cart for WP e-Commerce' ){
							echo '<a title="Learn more about '. esc_attr( $plug['name'] ) .'" target="_blank" href="http://getshopped.org/extend/premium-upgrades/premium-upgrades/gold-cart-plugin/">';
						} else echo '<a title="Learn more &amp; install '. esc_attr( $plug['name'] ) .'" class="thickbox" href="'. get_bloginfo('url') .'/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin='. $plug['stub'] .'&amp;TB_iframe=true&amp;width=640&amp;height=560">';
						echo esc_html($plug['name']) .'</a> : '. esc_html($plug['desc']) .'</li>';
					}
					?>
                    </ul>
                    <?php } ?>
                    <p><br /></p>
    <div class="clear"></div>
    </div>
	<?php
			break;
		default: ?>
	<div class="wrap">
    <div class="icon32" id="icon-themes"><br /></div><h2>Huh?</h2>
    </div>
    <?php
			break;
	}
}
endif;
if ( ! function_exists( 'progo_custom_login_logo' ) ):
/**
 * hooked to 'login_head' by add_action in progo_setup()
 * @since Ecommerce 1.0
 */
function progo_custom_login_logo() {
	if ( get_option('progo_logo') != '' ) {
		#needswork
		echo "<!-- login screen here... overwrite logo with custom logo -->\n"; 
	} else { ?>
<style type="text/css">
#login { margin-top: 6em; }
h1 a { background: url(<?php bloginfo( 'template_url' ); ?>/images/logo_progo.png) no-repeat top center; height: 80px; }
</style>
<?php }
}
endif;
if ( ! function_exists( 'progo_custom_login_url' ) ):
/**
 * hooked to 'login_headerurl' by add_action in progo_setup()
 * @uses get_option() To check if a custom logo has been uploaded to the back end
 * @return the custom URL
 * @since Ecommerce 1.0
 */
function progo_custom_login_url() {
	if ( get_option( 'progo_logo' ) != '' ) {
		return get_bloginfo( 'url' );
	} // else
	return 'http://www.progo.com';
}
endif;
if ( ! function_exists( 'progo_site_settings_page' ) ):
/**
 * outputs HTML for ProGo Themes "Site Settings" page
 * @uses settings_fields() for hidden form items for 'progo_options'
 * @uses do_settings_sections() for 'progo_site_settings'
 * @since Ecommerce 1.0
 */
function progo_site_settings_page() {
?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"></div>
		<h2>Site Settings</h2>
		<form action="options.php" method="post" enctype="multipart/form-data"><?php
		settings_fields( 'progo_options' );
		do_settings_sections( 'progo_site_settings' );
		?><p class="submit"><input type="submit" name="updateoption" value="Update &raquo;" /></p>
		</form>
	</div>
<?php
}
endif;
if ( ! function_exists( 'progo_home_slides_page' ) ):
/**
 * outputs HTML for ProGo Themes "Site Settings" page
 * @uses settings_fields() for hidden form items for 'progo_slides'
 * @uses do_settings_sections() for 'progo_home_slides'
 * @since Ecommerce 1.0
 */
function progo_home_slides_page() {
?>
	<div class="wrap">
		<div class="icon32" id="icon-themes"></div>
		<h2>Homepage Slides</h2>
		<form action="options.php" method="post" enctype="multipart/form-data"><?php
		settings_fields( 'progo_slides' );
		do_settings_sections( 'progo_home_slides' );
		?><p class="submit"><input type="submit" name="updateoption" value="Save Changes" class="button-primary" /></p>
		</form>
	</div>
<?php
}
endif;
if ( ! function_exists( 'progo_homeslide_start' ) ):
/**
 * helper function
 * @since Ecommerce 1.0
 */
function progo_homeslide_action($num, $sel, $slidedata = false) {
	$slideproduct = $slideimg = 0;
	$slidetext = '';
	if(is_array($slidedata)) {
		if(isset($slidedata['product'])) $slideproduct = absint($slidedata['product']);
		if(isset($slidedata['text'])) $slidetext = $slidedata['text'];
		if(isset($slidedata['image'])) $slideimg = absint($slidedata['image']);
	}
?><div class="postbox">
<div class="handlediv" title="Click to toggle"><br /></div><h3 class="hndle"><span>Slide <?php echo $num; ?></span></h3>
<div class="inside">
<p><a href="#" onclick="return progo_slideremove(jQuery(this));" style="float:right">Delete This Slide</a>Slide shows :<br /><select class="homeslideshows" name="progo_slides[<?php echo $num; ?>][show]" onchange="progo_slidefor(jQuery(this));"><option value="">- please select -</option>
<?php
$slidetypes = array(
	"product" => "Product",
	"text" => "Text Area"/*,
	"image" => "Image Banner"*/
);
foreach ( $slidetypes as $k => $v ) {
	$s = $sel == $k ? " selected='selected'" : "";
	echo "<option value='$k'$s>$v</option>";
}
?></select></p>
<p class="product" style="<?php if($sel!='product') echo 'display:none'; ?>">Select a Product for this Slide<br />
<select name="progo_slides[<?php echo $num; ?>][product]">
<?php
$prods = get_posts(array('numberposts' => -1, 'post_type' => 'wpsc-product'));
foreach ( $prods as $p ) {
	$s = $slideproduct == $p->ID ? ' selected="selected"' : '';
	echo '<option value="'. $p->ID .'"'. $s .'>'. esc_attr($p->post_title) .'</option>';
}
?>
</select></p>
<p class="text" style="<?php if($sel!='text') echo 'display:none'; ?>">Text to Display<br />
<textarea name="progo_slides[<?php echo $num; ?>][text]" rows="3" style="width: 100%"><?php echo esc_attr($slidetext); ?></textarea></p>
<p class="image" style="<?php if($sel!='image') echo 'display:none'; ?>">Choose an Image to display on this Slide. Images should be 960px width.<br />
<input type="hidden" name="progo_slides[<?php echo $num; ?>][image]" /></p>
</div>
</div>
<?php
}
endif;
if ( ! function_exists( 'progo_field_slides' ) ):
/**
 * outputs HTML for "Homepage Slides"
 * @since Ecommerce 1.0
 */
function progo_field_slides() {
	$slides = get_option( 'progo_slides' );
	$count = isset($slides['count']) ? absint($slides['count']) : 0;
	echo '<pre style="display:none">'. print_r($slides,true) .'</pre>';
	?>
<div id="poststuff" class="metabox-holder"><div id="normal-sortables" class="meta-box-sortables ui-sortable">
<?php
	if ( $count > 0 ) {
		unset($slides['count']);
		foreach($slides as $n => $s ) {
			progo_homeslide_action($n+1, $s['show'], $s);
		}
	}
?>
</div></div>
<p class="submit"><input type="submit" name="addmore" value="Add Another Slide &raquo;" onclick="return progo_anotherslide();" /><input type="hidden" name="progo_slides[count]" id="numslides" value="<?php echo $count; ?>" /></p>
<?php }
endif;
add_action('wp_ajax_progo_homeslide_ajax', 'progo_ajax_callback');
if(!function_exists('progo_ajax_callback')):
function progo_ajax_callback() {
	$slidenum = absint($_POST['slidenum']);
	$slideaction = $_POST['slideaction'];
	progo_homeslide_action($slidenum, $slideaction);

	die(); // this is required to return a proper result
}
endif;
if ( ! function_exists( 'progo_admin_page_styles' ) ):
/**
 * hooked to 'admin_print_styles' by add_action in progo_setup()
 * adds thickbox js for WELCOME screen styling
 * @since Ecommerce 1.0
 */
function progo_admin_page_styles() {
	global $pagenow;
	if ( $pagenow == 'themes.php' && isset( $_GET['page'] ) ) {
		if ( 'progo_admin' == $_GET['page'] ) {\
				wp_enqueue_style( 'global' );
				wp_enqueue_style( 'wp-admin' );
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_style( 'wp-e-commerce-admin', WPSC_URL .'/wpsc-admin/css/admin.css', false, false, 'all' );
		}
	}
	wp_enqueue_style( 'progo_admin', get_bloginfo( 'template_url' ) .'/css/admin-style.css' );
}
endif;
if ( ! function_exists( 'progo_admin_page_scripts' ) ):
/**
 * hooked to 'admin_print_scripts' by add_action in progo_setup()
 * adds thickbox js for WELCOME screen Recommended Plugin info
 * @since Ecommerce 1.0
 */
function progo_admin_page_scripts() {
	global $pagenow;
	if ( $pagenow == 'themes.php' && isset( $_GET['page'] ) ) {
		switch ( $_GET['page'] ) {
			case 'progo_admin':
        		wp_enqueue_script( 'thickbox' );
				break;
			case 'progo_home_slides':
				# here be drag'ns
				wp_enqueue_script('post');
				wp_enqueue_script('progo-homeslides-admin', get_bloginfo( 'template_url' ) .'/js/homeslides-admin.js', array ( 'jquery', 'post' ), false, true );
				break;
        }
	}
}
endif;
if ( ! function_exists( 'progo_admin_init' ) ):
/**
 * hooked to 'admin_init' by add_action in progo_setup()
 * sets admin action hooks
 * registers Site Settings
 * @since Ecommerce 1.0
 */
function progo_admin_init() {
	global $pagenow;
	if ( isset( $_REQUEST['progo_admin_action'] ) ) {
		switch( $_REQUEST['progo_admin_action'] ) {
			case 'reset_wpsc':
				progo_reset_wpsc(true);
				break;
			case 'reset_logo':
				progo_reset_logo();
				break;
			case 'no_taxes':
				progo_no_taxes();
				break;
			case 'no_shipping':
				progo_no_shipping();
				break;
			case 'colorBlackGrey':
				progo_colorscheme_switch( 'BlackGrey' );
				break;
			case 'colorGreyGreen':
				progo_colorscheme_switch( 'GreyGreen' );
				break;
			case 'colorBlackOrange':
				progo_colorscheme_switch( 'BlackOrange' );
				break;
			case 'colorLightBlue':
				progo_colorscheme_switch( 'LightBlue' );
				break;
			case 'colorGreenBrown':
				progo_colorscheme_switch( 'GreenBrown' );
				break;
			case 'permalink_recommended':
				progo_permalink_check( 'recommended' );
				break;
			case 'permalink_default':
				progo_permalink_check( 'default' );
				break;
		}
	}
	
	if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) ) {
		if ( $_GET['page'] == 'progo_admin' ) {
			wp_redirect( admin_url( 'themes.php?page=progo_admin' ) );
		}
	}
	
	// ACTION hooks
	add_action( 'admin_print_styles', 'progo_admin_page_styles' );
	add_action( 'admin_print_scripts', 'progo_admin_page_scripts' );
	
	// Installation (api key) settings
	// register_setting( 'progo_api_options', 'progo_api_options', 'progo_validate_options' );
	
	// Appearance settings
	register_setting( 'progo_options', 'progo_options', 'progo_validate_options' );
	
	add_settings_section( 'progo_api', 'ProGo Themes API Key', 'progo_section_text', 'progo_api' );
	add_settings_field( 'progo_api_key', 'API Key', 'progo_field_apikey', 'progo_api', 'progo_api' );
	
	add_settings_section( 'progo_theme', 'Theme Customization', 'progo_section_text', 'progo_theme' );
	add_settings_field( 'progo_colorscheme', 'Color Scheme', 'progo_field_color', 'progo_theme', 'progo_theme' );
	add_settings_field( 'progo_logo', 'Logo', 'progo_field_logo', 'progo_theme', 'progo_theme' );

	add_settings_section( 'progo_info', 'Site Info', 'progo_section_text', 'progo_info' );
	add_settings_field( 'progo_blogname', 'Site Name', 'progo_field_blogname', 'progo_info', 'progo_info' );
	add_settings_field( 'progo_blogdescription', 'Slogan', 'progo_field_blogdesc', 'progo_info', 'progo_info' );
	add_settings_field( 'progo_showdesc', 'Show/Hide Slogan', 'progo_field_showdesc', 'progo_info', 'progo_info' );
	add_settings_field( 'progo_support', 'Customer Support', 'progo_field_support', 'progo_info', 'progo_info' );
	add_settings_field( 'progo_copyright', 'Copyright Notice', 'progo_field_copyright', 'progo_info', 'progo_info' );
	add_settings_field( 'progo_secure', 'Security Logos', 'progo_field_cred', 'progo_info', 'progo_info' );
	add_settings_field( 'progo_companyinfo', 'Company Info', 'progo_field_compinf', 'progo_info', 'progo_info' );

	add_settings_section( 'progo_homepage', 'Homepage Settings', 'progo_section_text', 'progo_hometop' );
	add_settings_field( 'progo_frontpage', 'Display', 'progo_field_frontpage', 'progo_hometop', 'progo_homepage' );
	add_settings_field( 'progo_homeseconds', 'Slide Rotation Speed', 'progo_field_homeseconds', 'progo_hometop', 'progo_homepage' );
	
	// Homepage Slides settings
	register_setting( 'progo_slides', 'progo_slides', 'progo_validate_homeslides' );
	add_settings_section( 'progo_slide', 'Homepage Slides', 'progo_section_text', 'progo_home_slides' );
	add_settings_field( 'progo_make_slides', 'Homepage Slides', 'progo_field_slides', 'progo_home_slides', 'progo_slide' );
	
	// since there does not seem to be an actual THEME_ACTIVATION hook, we'll fake it here
	if ( get_option( 'progo_ecommerce_installed' ) != true ) {
		// also want to create a few other pages (Terms & Conditions, Privacy Policy), set up the FOOTER menu, and add these pages to it...
		
		$post_date = date( "Y-m-d H:i:s" );
		$post_date_gmt = gmdate( "Y-m-d H:i:s" );
		
		// create new menus in the Menu system
		$new_menus = array(
			'ftrlnx' => 'Footer Links',
			'mainmenu' => 'Main Menu'
		);
		$aok = 1;
		foreach ( $new_menus as $k => $m ) {
			$new_menus[$k] = wp_create_nav_menu($m);
			if ( is_numeric( $new_menus[$k] ) == false ) {
				$aok--;
			}
		}
		//set_theme_mod
		if ( $aok == 1 ) {
			// register the new menus as THE menus in theme's menu areas
			set_theme_mod( 'nav_menu_locations' , $new_menus );
		}
			
		// create a few new pages, and populate some menus
		$lipsum = "Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam...Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna\n\nLorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam...Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam";
		
		$new_pages = array(
			'home' => array(
				'title' => __( 'Home', 'progo' ),
				'content' => "<h3>This is your Homepage</h3>$lipsum",
				'id' => '',
				'menu' => 'mainmenu'
			),
			'about' => array(
				'title' => __( 'About', 'progo' ),
				'content' => "<h3>This Page could have info about your site/store</h3>$lipsum",
				'id' => '',
				'menu' => 'mainmenu'
			),
			'blog' => array(
				'title' => __( 'Blog', 'progo' ),
				'content' => "This Page pulls in your Blog posts",
				'id' => '',
				'menu' => 'mainmenu'
			),
			'terms' => array(
				'title' => __( 'Terms & Conditions', 'progo' ),
				'content' => "<h3>List your Terms and Conditions here</h3>$lipsum",
				'id' => '',
				'menu' => 'ftrlnx'
			),
			'privacy' => array(
				'title' => __( 'Privacy Policy', 'progo' ),
				'content' => "<h3>Put your Privacy Policy here</h3>$lipsum",
				'id' => '',
				'menu' => 'ftrlnx'
			),
			'customer-service' => array(
				'title' => __( 'Customer Service', 'progo' ),
				'content' => "<h3>This Page could have Customer Service info on it</h3>$lipsum",
				'id' => '',
				'menu' => 'ftrlnx'
			)
		);
		foreach ( $new_pages as $slug => $page ) {
			$new_pages[$slug]['id'] = wp_insert_post( array(
				'post_title' 	=>	$page['title'],
				'post_type' 	=>	'page',
				'post_name'		=>	$slug,
				'comment_status'=>	'closed',
				'ping_status' 	=>	'closed',
				'post_content' 	=>	$page['content'],
				'post_status' 	=>	'publish',
				'post_author' 	=>	1,
				'menu_order'	=>	1
			));
			
			if ( $new_pages[$slug]['id'] != false ) {
				// set "Home" & "Blog" page IDs
				switch ( $slug ) {
					case 'home':
						update_option( 'page_on_front', $new_pages[$slug]['id'] );
						update_option( 'progo_homepage_id', $new_pages[$slug]['id'] );
						break;
					case 'blog':
						update_option( 'page_for_posts', $new_pages[$slug]['id'] );
						update_option( 'progo_blog_id', $new_pages[$slug]['id'] );
						break;
				}
				
				$menu_args = array(
					'menu-item-object-id' => $new_pages[$slug]['id'],
					'menu-item-object' => 'page',
					'menu-item-parent-id' => 0,
					'menu-item-type' => 'post_type',
					'menu-item-title' => $page['title'],
					'menu-item-status' => 'publish',
				);
				$menu_id = $new_menus[$new_pages[$slug]['menu']];
				if ( is_numeric( $menu_id ) ) {
					wp_update_nav_menu_item( $menu_id , 0, $menu_args );
				}
			}
		}
		// set our default SITE options
		progo_options_defaults();
		
		// and send to WELCOME page
		wp_redirect( get_option( 'siteurl' ) . '/wp-admin/themes.php?page=progo_admin' );
	}
}
endif;
if ( ! function_exists( 'progo_ecommerce_widgets' ) ):
/**
 * registers a sidebar area for the WIDGETS page
 * and registers various Widgets
 * @since Ecommerce 1.0
 */
function progo_ecommerce_widgets() {
	register_sidebar(array(
		'name' => 'Main Sidebar',
		'id' => 'main',
		'description' => 'Standard right column sidebar area',
		'before_widget' => '<div class="block %1$s %2$s">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="title"><span class="spacer">',
		'after_title' => '</span></h3><div class="inside">'
	));
	register_sidebar(array(
		'name' => 'Blog',
		'id' => 'blog',
		'description' => 'Sidebar for the Blog area',
		'before_widget' => '<div class="block %1$s %2$s">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="title"><span class="spacer">',
		'after_title' => '</span></h3><div class="inside">'
	));
	register_sidebar(array(
		'name' => 'Checkout',
		'id' => 'checkout',
		'description' => 'Streamline your Checkout process',
		'before_widget' => '<div class="block %1$s %2$s">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="title"><span class="spacer">',
		'after_title' => '</span></h3><div class="inside">'
	));
	register_sidebar(array(
		'name' => 'Contact',
		'id' => 'contact',
		'description' => 'Optional Contact/About page sidebar',
		'before_widget' => '<div class="block %1$s %2$s">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="title"><span class="spacer">',
		'after_title' => '</span></h3><div class="inside">'
	));
	register_sidebar(array(
		'name' => 'Header',
		'id' => 'header',
		'description' => 'We can put a widget or two in the top right of the header',
		'before_widget' => '<div class="hblock %1$s %2$s">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="title"><span class="spacer">',
		'after_title' => '</span></h3><div class="inside">'
	));
	register_sidebar(array(
		'name' => 'Footer',
		'id' => 'fbar',
		'description' => 'On the bottom of each page. If left blank, will display the Main Menu.',
		'before_widget' => '<div class="fblock %1$s %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<div class="title">',
		'after_title' => '</div>'
	));
	
	$progo_widgets = array( 'FBLikeBox', 'Tweets', 'Share', 'Social', 'Support' );
	foreach ( $progo_widgets as $w ) {
		require_once( 'widgets/widget-'. strtolower($w) .'.php' );
		register_widget( 'ProGo_Widget_'. $w );
	}
}
endif;
if ( ! function_exists( 'progo_metabox_cleanup' ) ):
/**
 * fires after wpsc_meta_boxes hook, so we can overwrite a lil bit
 * @since Ecommerce 1.0
 */
function progo_metabox_cleanup() {
	global $wp_meta_boxes;
	global $post_type;
	global $post;
	
	switch($post_type) {
		case 'wpsc-product':
			if ( isset( $wp_meta_boxes['wpsc-product'] ) ) {
				// unhook wpsc's Product Images metabox and add our own instead
				remove_meta_box( 'wpsc_product_image_forms', 'wpsc-product', 'normal' );
				add_meta_box( 'progo_product_image_forms', 'Product Images', 'progo_product_image_forms', 'wpsc-product', 'normal', 'high' );
				// sort the wpsc-product main column meta boxes so Product Images is #1
				$wp_meta_boxes['wpsc-product']['normal']['high'] = progo_arraytotop( $wp_meta_boxes['wpsc-product']['normal']['high'], 'progo_product_image_forms' );
				
				// also move PRICE to just under SUBMITdiv on right
				// Backup and delete element from parent array
				$toparr = array(
					'submitdiv' => $wp_meta_boxes['wpsc-product']['side']['core']['submitdiv'],
					'wpsc_price_control_forms' => $wp_meta_boxes['wpsc-product']['side']['low']['wpsc_price_control_forms']
				);
				unset($wp_meta_boxes['wpsc-product']['side']['core']['submitdiv']);
				unset($wp_meta_boxes['wpsc-product']['side']['low']['wpsc_price_control_forms']);
				// Merge the two arrays together so our widget is at the beginning
				$wp_meta_boxes['wpsc-product']['side']['core'] = array_merge( $toparr, $wp_meta_boxes['wpsc-product']['side']['core'] );
			}
			break;
		case 'page':
			add_meta_box( 'progo_sidebar_box', 'Sidebar', 'progo_sidebar_box', 'page', 'side', 'low' );
			break;
	}
}
endif;
add_action( 'do_meta_boxes', 'progo_metabox_cleanup' );
if ( ! function_exists( 'progo_sidebar_box' ) ):
/**
 * outputs html for "Sidebar" meta box on EDIT PAGE
 * lets Admins choose which Sidebar area is displayed on each Page
 * called by add_meta_box( "progo_direct_box", "Direct Response", "progo_direct_box"...
 * in progo_admin_init()
 * @uses progo_direct_meta_defaults()
 * @since Direct 1.0.9
 */
function progo_sidebar_box() {
	global $post;
	$custom = get_post_meta($post->ID,'_progo_sidebar');
	$sidebar = $custom[0];
	
	$ids = array('main', 'blog', 'checkout', 'contact');
	$titles = array('Main Sidebar (default)', 'Blog', 'Checkout', 'Contact');
	
	if(!in_array($sidebar, $ids)) {
		$sidebar = 'main';
	}
	?>
	<p>Choose a Sidebar to display on this Page</p>
	<select name="_progo_sidebar"><?php
for ( $i = 0; $i < count($ids); $i++) {
		echo '<option value="'. $ids[$i] .'"'. ( $ids[$i] == $sidebar ? ' selected="selected"' : '' ) .'>'. esc_attr( $titles[$i] ) .'</option>';
	} ?></select>
    <p><a href="<?php echo admin_url('widgets.php'); ?>" target="_blank">Configure Widgets Here &raquo;</a></p>
	<?php
}
endif;

/********* core ProGo Themes' Ecommerce functions *********/

if ( ! function_exists( 'progo_add_scripts' ) ):
/**
 * hooked to 'wp_print_scripts' by add_action in progo_setup()
 * adds front-end js
 * @since BookIt 1.0
 */
function progo_add_scripts() {
	if ( !is_admin() ) {
		wp_register_script( 'progo', get_bloginfo('template_url') .'/js/progo-frontend.js', array('jquery'), '1.0' );
		wp_enqueue_script( 'progo' );
		do_action('progo_frontend_scripts');
	} else {
		//
	}
}
endif;

if ( ! function_exists( 'progo_colorschemes' ) ):
/**
 * @return array of Color Schemes
 * @since Ecommerce 1.0
 */
function progo_colorschemes() {
	return array( 'BlackGrey', 'GreyGreen', 'BlackOrange', 'LightBlue', 'GreenBrown' );
}
endif;
if ( ! function_exists( 'progo_add_styles' ) ):
/**
 * hooked to 'wp_print_styles' by add_action in progo_setup()
 * checks for Color Scheme setting and adds appropriate front-end stylesheet
 * @since Ecommerce 1.0
 */
function progo_add_styles() {
	if ( !is_admin() ) {
		$options = get_option('progo_options');
		$color = $options['colorscheme'];
		$avail = progo_colorschemes();
		if ( in_array( $color, $avail ) ) {
			$scheme = 'proGoColorscheme'. $color;
			wp_register_style( $scheme, get_bloginfo('template_url') .'/css/style'. $color .'.css' );
			wp_enqueue_style( $scheme );
		}
	}
	do_action('progo_frontend_styles');
}
endif;
if ( ! function_exists( 'progo_reset_wpsc' ) ):
/**
 * sets WPSC image/thumbnail sizes to ProGo recommended settings
 * also updates wpsc_email_receipt
 * @since Ecommerce 1.0
 */
function progo_reset_wpsc($fromlink = false){
	if ( $fromlink == true ) {
		check_admin_referer( 'progo_reset_wpsc' );
	}
	//set thumbnail & main image size to desired dimensions
	update_option( 'product_image_width', 70 );
	update_option( 'product_image_height', 70 );
	update_option( 'single_view_image_width', 290 );
	update_option( 'single_view_image_height', '' );
	
	update_option( 'wpsc_email_receipt', "Any items to be shipped will be processed as soon as possible, any items that can be downloaded can be downloaded using the links on this page. All prices include tax and postage and packaging where applicable.\n\n%product_list%%total_price%%find_us%" );
	
	if ( $fromlink == true ) {
		wp_redirect( get_option('siteurl') .'/wp-admin/themes.php?page=progo_admin' );
		exit();
	}
}
endif;
if ( ! function_exists( 'progo_reset_logo' ) ):
/**
 * wipe out any custom logo image setting
 * @since Ecommerce 1.0
 */
function progo_reset_logo(){
	check_admin_referer( 'progo_reset_logo' );
	
	// reset logo settings
	$options = get_option('progo_options');
	$options['logo'] = '';
	update_option( 'progo_options', $options );
	update_option( 'progo_settings_just_saved', 1 );
	
	wp_redirect( get_option('siteurl') .'/wp-admin/themes.php?page=progo_admin' );
	exit();
}
endif;
if ( ! function_exists( 'progo_no_taxes' ) ):
/**
 * @since Ecommerce 1.1.23
 */
function progo_no_taxes(){
	check_admin_referer( 'progo_no_taxes' );
	
	update_option( 'progo_ecommerce_notaxes', true );
	
	wp_redirect( admin_url("options-general.php?page=wpsc-settings&tab=taxes") );
	exit();
}
endif;
if ( ! function_exists( 'progo_no_shipping' ) ):
/**
 * @since Ecommerce 1.1.23
 */
function progo_no_shipping(){
	check_admin_referer( 'progo_no_shipping' );
	
	update_option( 'progo_ecommerce_noshipping', true );
	
	wp_redirect( admin_url("options-general.php?page=wpsc-settings&tab=shipping") );
	exit();
}
endif;
 //recommended | default :: progo_permalink_check
if ( ! function_exists( 'progo_permalink_check' ) ):
/**
 * @since Ecommerce 1.1.23
 */
function progo_permalink_check( $arg ){
	check_admin_referer( 'progo_permalink_check' );
	
	if ( $arg == 'recommended' ) {
		update_option( 'permalink_structure', '/%year%/%monthnum%/%day%/%postname%/' );
	} elseif ( $arg == 'default' ) {
		update_option( 'progo_permalink_checked', true );
	}
	wp_redirect( admin_url("options-permalink.php") );
	exit();
}
endif;
if ( ! function_exists( 'progo_arraytotop' ) ):
/**
 * helper function to bring a given element to the start of an array
 * @param parent array
 * @param element to bring to the top
 * @return sorted array
 * @since Ecommerce 1.0
 */
function progo_arraytotop($arr, $totop) {
	// Backup and delete element from parent array
	$toparr = array($totop => $arr[$totop]);
	unset($arr[$totop]);
	// Merge the two arrays together so our widget is at the beginning
	return array_merge( $toparr, $arr );
}
endif;
if ( ! function_exists( 'progo_save_meta' ) ):
/**
 * hooked to 'save_post' by add_action in progo_setup()
 * checks for _progo (direct) meta data, and performs validation & sanitization
 * @param post_id to check meta on
 * @return post_id
 * @since Ecommerce 1.0.9
 */
function progo_save_meta( $post_id ){
	// verify if this is an auto save routine. If it is,
	// our form has not been submitted, so we don't want to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) { 
		return $post_id;
	}
	// check permissions
	if ( $_POST['post_type'] == 'page' ) {
		if ( !current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} else {
	//if ( !current_user_can( 'edit_post', $post_id ) )
	  return $post_id;
	}
	
	// OK, we're authenticated: we need to find and save the data
	if ( isset( $_POST['_progo_sidebar'] ) ) {
		$sidebar = $_POST['_progo_sidebar'];
		
		if ( in_array ( $sidebar, array('main', 'blog', 'checkout', 'contact') ) ) {
			update_post_meta($post_id, "_progo_sidebar", $sidebar);
			return $sidebar;
		}
	}
	return $post_id;
}
endif;
if ( ! function_exists( 'progo_colorscheme_switch' ) ):
/**
 * helper function to switch the current Color Scheme
 * @since Ecommerce 1.0.52
 */
function progo_colorscheme_switch( $color ) {
	$okgo = true;
	$avail = progo_colorschemes();
	if( current_user_can('manage_options') == false ) {
		$okgo = false;
	} elseif ( in_array($color, $avail) == false ) {
		$okgo = false;
	}
	
	if ( $okgo == true ) {
		$opt = get_option( 'progo_options' );
		$opt[colorscheme] = $color;
		update_option( 'progo_options', $opt );
		
		wp_redirect( get_option('siteurl') );
	} else {
		wp_die('Nice try...');
		return;
	}
}
endif;
/**
 * ProGo Site Settings Options defaults
 * @since Ecommerce 1.0
 */
function progo_options_defaults() {
	// Define default option settings
	$tmp = get_option( 'progo_options' );
    if ( !is_array( $tmp ) ) {
		$def = array(
			"colorscheme" => "BlackGrey",
			"logo" => "",
			"blogname" => get_option( 'blogname' ),
			"blogdescription" => get_option( 'blogdescription' ),
			"showdesc" => 1,
			"support" => "123-555-7890",
			"copyright" => "© Copyright 2011, All Rights Reserved",
			"credentials" => "",
			"companyinfo" => "We sincerely thank you for your patronage.\nThe Our Company Staff\n\nOur Company, Inc.\n1234 Address St\nSuite 43\nSan Diego, CA 92107\n619-555-5555",
			"frontpage" => get_option( 'show_on_front' ),
			"homeseconds" => 6
		);
		update_option( 'progo_options', $def );
	}
	$tmp = get_option( 'progo_slides' );
    if ( !is_array( $tmp ) ) {
		$def = array('count'=>0);	
		update_option( 'progo_slides', $def );
	}
	
	update_option( 'progo_ecommerce_installed', true );
	update_option( 'progo_ecommerce_apikey', '' );
	update_option( 'progo_ecommerce_apiauth', '100' );
	
	update_option( 'wpsc_ignore_theme', true );
	
	// set large image size
	update_option( 'large_size_w', 650 );
	update_option( 'large_size_h', 413 );
	
	// no SHARETHIS automatically all over the place?
	update_option( 'st_add_to_content', 'no' );
	update_option( 'st_add_to_page', 'no' );
}
if ( ! function_exists( 'progo_validate_homeslides' ) ):
/**
 * ProGo Homeslides Options settings validation function
 * @param $input options to validate
 * @return $input after validation has taken place
 * @since Ecommerce 1.0
 */
function progo_validate_homeslides( $input ) {
	$counto = absint( $input['count'] );
	unset( $input['count'] );
	$newslides = array();
	$count = 0;
	foreach ( $input as $slide ) {
		$newslide = array();
		$newslide['show'] = $slide['show'];
		$newslide['product'] = isset($slide['product']) ? absint($slide['product']) : 0;
		$newslide['text'] = isset($slide['text']) ? wp_kses($slide['text'], array()) : '';
		$newslide['image'] = isset($slide['image']) ? absint($slide['image']) : 0;
		$newslides[] = $newslide;
		$count++;
	}
	// check for new slide addition ...
	for ( $i = $count; $i < $counto; $i++ ) {
		$newslides[] = array(
			'show' => '',
			'product' => 0,
			'text' => '',
			'image' => 0
		);
	}
	$newslides['count'] = $counto;
	$input = $newslides;
	return $input;
}
endif;
if ( ! function_exists( 'progo_validate_options' ) ):
/**
 * ProGo Site Settings Options validation function
 * from register_setting( 'progo_options', 'progo_options', 'progo_validate_options' );
 * in progo_admin_init()
 * also handles uploading of custom Site Logo
 * @param $input options to validate
 * @return $input after validation has taken place
 * @since Ecommerce 1.0
 */
function progo_validate_options( $input ) {
	if( isset($input['apikey']) ) {
		$input['apikey'] = wp_kses( $input['apikey'], array() );
		// store API KEY in its own option
		if ( $input['apikey'] != get_option( 'progo_ecommerce_apikey' ) ) {
			update_option( 'progo_ecommerce_apikey', substr( $input['apikey'], 0, 39 ) );
		}
	}
	
		// do validation here...
	$arr = array( 'blogname', 'blogdescription', 'colorscheme', 'support', 'copyright', 'companyinfo' );
	foreach ( $arr as $opt ) {
		$input[$opt] = wp_kses( $input[$opt], array() );
	}
	
	// opt[colorscheme] must be one of the allowed colors
	$colors = progo_colorschemes();
	if ( !in_array( $input['colorscheme'], $colors ) ) {
		$input['colorscheme'] = 'BlackGrey';
	}
	
	$choices = array(
		'posts',
		'featured',
		'page'
	);
	if ( !in_array( $input['frontpage'], $choices ) ) {
		$input['frontpage'] = get_option('show_on_front');
	}
	switch ( $input['frontpage'] ) {
		case 'posts':
			update_option( 'show_on_front', 'posts' );
			break;
		case 'featured':
		case 'page':
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', get_option('progo_homepage_id') );
			break;
	}
	
	// opt[showdesc] can only be 1 or 0
	if ( (int) $input['showdesc'] != 1 ) {
		$input['showdesc'] = 0;
	}
	
	// save blogname & blogdescription to other options as well
	$arr = array( 'blogname', 'blogdescription' );
	foreach ( $arr as $opt ) {
		if ( $input[$opt] != get_option( $opt ) ) {
			update_option( $opt, $input[$opt] );
		}
	}
	
	// check SUPPORT field & set option['support_email'] flag if we have an email
	$input['support_email'] = is_email( $input['support'] );
	
		// upload error?
		$error = '';
	// upload the file - BASED OFF WP USERPHOTO PLUGIN
	if ( isset($_FILES['progo_options']) && @$_FILES['progo_options']['name']['logotemp'] ) {
		if ( $_FILES['progo_options']['error']['logotemp'] ) {
			switch ( $_FILES['progo_options']['error']['logotemp'] ) {
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$error = "The uploaded file exceeds the max upload size.";
					break;
				case UPLOAD_ERR_PARTIAL:
					$error = "The uploaded file was only partially uploaded.";
					break;
				case UPLOAD_ERR_NO_FILE:
					$error = "No file was uploaded.";
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$error = "Missing a temporary folder.";
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$error = "Failed to write file to disk.";
					break;
				case UPLOAD_ERR_EXTENSION:
					$error = "File upload stopped by extension.";
					break;
				default:
					$error = "File upload failed due to unknown error.";
			}
		} elseif ( !$_FILES['progo_options']['size']['logotemp'] ) {
			$error = "The file &ldquo;". $_FILES['progo_options']['name']['logotemp'] ."&rdquo; was not uploaded. Did you provide the correct filename?";
		} elseif ( !in_array( $_FILES['progo_options']['type']['logotemp'], array( "image/jpeg", "image/pjpeg", "image/gif", "image/png", "image/x-png" ) ) ) {
			$error = "The uploaded file type &ldquo;". $_FILES['progo_options']['type']['logotemp'] ."&rdquo; is not allowed.";
		}
		$tmppath = $_FILES['progo_options']['tmp_name']['logotemp'];
		
		$imageinfo = null;
		if(!$error){			
			$imageinfo = getimagesize($tmppath);
			if ( !$imageinfo || !$imageinfo[0] || !$imageinfo[1] ) {
				$error = __("Unable to get image dimensions.", 'user-photo');
			} else if( $imageinfo[0] > 598 || $imageinfo[1] > 75 ) {
				/*
				if(userphoto_resize_image($tmppath, null, $userphoto_maximum_dimension, $error)) {
					$imageinfo = getimagesize($tmppath);
				}
				*/
				$filename = $tmppath;
				$newFilename = $filename;
				$jpeg_compression = 86;
				#if(empty($userphoto_jpeg_compression))
				#	$userphoto_jpeg_compression = USERPHOTO_DEFAULT_JPEG_COMPRESSION;
				
				$info = @getimagesize($filename);
				if(!$info || !$info[0] || !$info[1]){
					$error = __("Unable to get image dimensions.", 'user-photo');
				}
				//From WordPress image.php line 22
				else if (
					!function_exists( 'imagegif' ) && $info[2] == IMAGETYPE_GIF
					||
					!function_exists( 'imagejpeg' ) && $info[2] == IMAGETYPE_JPEG
					||
					!function_exists( 'imagepng' ) && $info[2] == IMAGETYPE_PNG
				) {
					$error = __( 'Filetype not supported.', 'user-photo' );
				}
				else {
					// create the initial copy from the original file
					if ( $info[2] == IMAGETYPE_GIF ) {
						$image = imagecreatefromgif( $filename );
					}
					elseif ( $info[2] == IMAGETYPE_JPEG ) {
						$image = imagecreatefromjpeg( $filename );
					}
					elseif ( $info[2] == IMAGETYPE_PNG ) {
						$image = imagecreatefrompng( $filename );
					}
					if(!isset($image)){
						$error = __("Unrecognized image format.", 'user-photo');
						return false;
					}
					if ( function_exists( 'imageantialias' ))
						imageantialias( $image, TRUE );
			
					// make sure logo is within max 598 x 75 dimensions
					
					// figure out the longest side
					if ( ( $info[0] / $info[1] ) > 8 ) { // resize width to fit 
						$image_width = $info[0];
						$image_height = $info[1];
						$image_new_width = 598;
			
						$image_ratio = $image_width / $image_new_width;
						$image_new_height = round( $image_height / $image_ratio );
					} else { // resize height to fit
						$image_width = $info[0];
						$image_height = $info[1];
						$image_new_height = 75;
			
						$image_ratio = $image_height / $image_new_height;
						$image_new_width = round( $image_width / $image_ratio );
					}
			
					$imageresized = imagecreatetruecolor( $image_new_width, $image_new_height);
					@ imagecopyresampled( $imageresized, $image, 0, 0, 0, 0, $image_new_width, $image_new_height, $info[0], $info[1] );
			
					// move the thumbnail to its final destination
					if ( $info[2] == IMAGETYPE_GIF ) {
						if (!imagegif( $imageresized, $newFilename ) ) {
							$error = __( "Logo path invalid" );
						}
					}
					elseif ( $info[2] == IMAGETYPE_JPEG ) {
						if (!imagejpeg( $imageresized, $newFilename, $jpeg_compression ) ) {
							$error = __( "Logo path invalid" );
						}
					}
					elseif ( $info[2] == IMAGETYPE_PNG ) {
						@ imageantialias($imageresized,true);
						@ imagealphablending($imageresized, false);
						@ imagesavealpha($imageresized,true);
						$transparent = imagecolorallocatealpha($imageresized, 255, 255, 255, 0);
						for($x=0;$x<$image_new_width;$x++) {
							for($y=0;$y<$image_new_height;$y++) {
							@ imagesetpixel( $imageresized, $x, $y, $transparent );
							}
						}
						@ imagecopyresampled( $imageresized, $image, 0, 0, 0, 0, $image_new_width, $image_new_height, $info[0], $info[1] );

						if (!imagepng( $imageresized, $newFilename ) ) {
							$error = __( "Logo path invalid" );
						}
					}
				}
				if(empty($error)) {
					$imageinfo = getimagesize($tmppath);
				}
			}
		}
		
		if ( !$error ){
			$upload_dir = wp_upload_dir();
			$dir = trailingslashit( $upload_dir['basedir'] );
			$imagepath = $dir . $_FILES['progo_options']['name']['logotemp'];
			
			if ( !move_uploaded_file( $tmppath, $imagepath ) ) {
				$error = "Unable to place the user photo at: ". $imagepath;
			}
			else {
				chmod($imagepath, 0666);
				
				$input['logo'] = $_FILES['progo_options']['name']['logotemp'];
	
				/*
				if($oldFile && $oldFile != $newFile)
					@unlink($dir . '/' . $oldFile);
				*/
			}
		}
	}
	update_option('progo_settings_just_saved',1);
	
	return $input;
}
endif;

/********* more helper functions *********/

if ( ! function_exists( 'progo_field_color' ) ):
/**
 * outputs HTML for "Color Scheme" option on Site Settings page
 * @uses progo_colorschemes() for list of available Color Schemes
 * @since Ecommerce 1.0
 */
function progo_field_color() {
	$options = get_option( 'progo_options' );
	$opts = progo_colorschemes();
	// in case a child theme overwrites the Available Colors progo_colorschemes() function
	if( count($opts) > 0 ) {
	?>
<select id="progo_colorscheme" name="progo_options[colorscheme]" style="float: left; margin-right: 20px; width: 128px;" onchange="updateScreenshot()">
<?php
	foreach ( $opts as $color ) {
		echo '<option value="'. $color .'"'. (($options['colorscheme']==$color) ? ' selected="selected"' : '') .'>'.esc_html($color).'</option>';
	}
?></select><script type="text/javascript">
function updateScreenshot() {
	var color = jQuery('#progo_colorscheme').val();
	jQuery('#progo_color_thm').attr('src','<?php bloginfo('template_url'); ?>/images/'+ color +'/screenshot-thm.jpg');
}

jQuery(function($) {
	$('#progo_colorscheme').after('<img id="progo_color_thm" style="border:1px solid #DFDFDF; width: 150px" />').parent().attr('valign','top');
	updateScreenshot();
});
</script>
<?php } else {
		echo 'COLOR SCHEMES OPTION HAS BEEN OVERWRITTEN';
	}
}
endif;
if ( ! function_exists( 'progo_field_logo' ) ):
/**
 * outputs HTML for custom "Logo" on Site Settings page
 * @since Ecommerce 1.0
 */
function progo_field_logo() {
	$options = get_option('progo_options');
	if ( $options['logo'] != '' ) {
		$upload_dir = wp_upload_dir();
		$dir = trailingslashit( $upload_dir['baseurl'] );
		$imagepath = $dir . $options['logo'];
		echo '<img src="'. esc_attr( $imagepath ) .'" /> [<a href="'. wp_nonce_url("admin.php?progo_admin_action=reset_logo", 'progo_reset_logo') .'">Delete Logo</a>]<br /><span class="description">Replace Logo</span><br />';
	} ?>
<input type="hidden" id="progo_logo" name="progo_options[logo]" value="<?php echo esc_attr( $options['logo'] ); ?>" />
<input type="file" id="progo_logotemp" name="progo_options[logotemp]" />
<span class="description">Upload your logo here.<br />
Maximum dimensions: 598px Width x 75px Height. Larger images will be automatically scaled down to fit size.<br />
Maximum upload file size: <?php echo ini_get( "upload_max_filesize" ); ?>. Allowable formats: gif/jpg/png. Transparent png's / gif's are recommended.</span>
<?php
#needswork
}
endif;

/**
 * outputs HTML for "API Key" field on Site Settings page
 * @since Ecommerce 1.0
 */
function progo_field_apikey() {
	$opt = get_option( 'progo_ecommerce_apikey', true );
	echo '<input id="apikey" name="progo_options[apikey]" class="regular-text" type="text" value="'. esc_html( $opt ) .'" maxlength="39" />';
	$apiauth = get_option( 'progo_ecommerce_apiauth', true );
	switch($apiauth) {
		case 100:
			echo ' <img src="'. get_bloginfo('template_url') .'/images/check.png" alt="aok" class="kcheck" />';
			break;
		default:
			echo ' <img src="'. get_bloginfo('template_url') .'/images/x.gif" alt="X" class="kcheck" title="'. $apiauth .'" />';
			break;
	}
	echo '<br /><span class="description">You API Key was sent via email when you purchased the Ecommerce theme from ProGo Themes.</span><br /><br />ProGo Themes are Easy and Quick to Set Up using our Step-by-Step Process.<br /><a href="http://www.progo.com/resources/QuickStartGuide-Ecommerce.pdf" target="_blank">Download the ProGo Ecommerce Theme Quick Start Guide (PDF)</a>';
}

if ( ! function_exists( 'progo_field_blogname' ) ):
/**
 * outputs HTML for "Site Name" field on Site Settings page
 * @since Ecommerce 1.0
 */
function progo_field_blogname() {
	$opt = get_option( 'blogname' );
	echo '<input id="blogname" name="progo_options[blogname]" class="regular-text" type="text" value="'. esc_html( $opt ) .'" />';
}
endif;
if ( ! function_exists( 'progo_field_blogdesc' ) ):
/**
 * outputs HTML for "Slogan" field on Site Settings page
 * @since Ecommerce 1.0
 */
function progo_field_blogdesc() {
	$opt = get_option( 'blogdescription' ); ?>
<input id="blogdescription" name="progo_options[blogdescription]" class="regular-text" type="text" value="<?php esc_html_e( $opt ); ?>" />
<?php }
endif;
if ( ! function_exists( 'progo_field_showdesc' ) ):
/**
 * outputs HTML for checkbox "Show Slogan" field on Site Settings page
 * @since Ecommerce 1.0
 */
function progo_field_showdesc() {
	$options = get_option( 'progo_options' ); ?>
<fieldset><legend class="screen-reader-text"><span>Show Slogan</span></legend><label for="progo_showdesc">
<input type="checkbox" value="1" id="progo_showdesc" name="progo_options[showdesc]"<?php
	if ( (int) $options['showdesc'] == 1 ) {
		echo ' checked="checked"';
	} ?> />
Show the Site Slogan next to the Logo at the top of <a target="_blank" href="<?php echo esc_url( trailingslashit( get_bloginfo( 'url' ) ) ); ?>">your site</a></label>
</fieldset>
<?php }
endif;
if ( ! function_exists( 'progo_field_support' ) ):
/**
 * outputs HTML for "Customer Support" field on Site Settings page
 * @since Ecommerce 1.0
 */
function progo_field_support() {
	$options = get_option( 'progo_options' );
	?>
<input id="progo_support" name="progo_options[support]" value="<?php esc_html_e( $options['support'] ); ?>" class="regular-text" type="text" />
<span class="description">Enter either a Phone # (like <em>222-333-4444</em>) or email address</span>
<?php }
endif;
if ( ! function_exists( 'progo_field_copyright' ) ):
/**
 * outputs HTML for "Copyright Notice" field on Site Settings page
 * @since Ecommerce 1.0
 */
function progo_field_copyright() {
	$options = get_option( 'progo_options' );
	?>
<input id="progo_copyright" name="progo_options[copyright]" value="<?php esc_html_e( $options['copyright'] ); ?>" class="regular-text" type="text" />
<span class="description">Copyright notice that appears on the right side of your site's footer.</span>
<?php }
endif;
if ( ! function_exists( 'progo_field_cred' ) ):
/**
 * outputs HTML for "Security Logos" field on Site Settings page
 * @since Ecommerce 1.0
 */
function progo_field_cred() {
	$options = get_option( 'progo_options' ); ?>
<textarea id="progo_secure" name="progo_options[credentials]" style="width: 95%;"><?php esc_html_e( $options['credentials'] ); ?></textarea><br />
<span class="description">Security Logos can help increase your site's conversion by over 20%. Paste any code that is associated with generating your credentials in the text box above. Please separate each credential's code by a space (ie. "&lt;script type="text/javascript" src="https://godaddy.com/..."&gt;&lt;/span&gt; &lt;script type="text/javascript" src="http://www.verisign.com/..."&gt;&lt;/script&gt;").</span>
<?php }
endif;
if ( ! function_exists( 'progo_field_compinf' ) ):
/**
 * outputs HTML for "Security Logos" field on Site Settings page
 * @since Ecommerce 1.0
 */
function progo_field_compinf() {
	$options = get_option( 'progo_options' ); ?>
<textarea id="progo_companyinfo" name="progo_options[companyinfo]" style="width: 95%;" rows="5"><?php esc_html_e( $options['companyinfo'] ); ?></textarea><br />
<span class="description">This text appears at the end of Transaction Results pages and email receipts.</span>
<?php }
endif;
if ( ! function_exists( 'progo_field_frontpage' ) ):
/**
 * outputs HTML for Homepage "Displays" field on Site Settings page
 * @since Ecommerce 1.0
 */
function progo_field_frontpage() {
	// Latest Blog Posts, (Featured Products), Static Content
	$choices = array(
		'posts' => 'Latest Blog Posts',
		'featured' => 'Featured Products',
		'page' => 'Static Content'
	);
	$msgs = array(
		'posts' => '<a href="edit.php">Edit Posts Here</a>',
		'featured' => 'Designate Featured Products by clicking on the Star in the Featured column on your <a href="edit.php?post_type=wpsc-product">Products Page</a>',
		'page' => '<a href="post.php?post='. get_option('progo_homepage_id') .'&action=edit">Edit Homepage Content Here</a>'
	);
	$msg = '';
	if ( !function_exists('wpsc_admin_pages')) {
		unset($choices['featured']);
		$msg .= 'Your Homepage can display "Featured" Products, but WP E-Commerce Plugin appears to be inactive. ';
		$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url('plugins.php?action=activate&amp;plugin=wp-e-commerce/wp-shopping-cart.php&amp;plugin_status=all&amp;paged=1', 'activate-plugin_wp-e-commerce/wp-shopping-cart.php') : 'plugins.php';
		$msg .= '<a href="'. esc_url($lnk) .'">Click Here to Activate</a>';
	}
	
//	$msg .= '<pre>'. print_r(get_option('show_on_front'),true)  .'</pre>'. print_r(get_option('page_on_front'),true) .'</pre>'. print_r(get_option('page_for_posts'),true) .'</pre>';
	
	$options = get_option( 'progo_options' );
	// check just in case show_on_front changed since this was last updated?
	// $options['frontpage'] = get_option('show_on_front');
	
	?><p><select id="progo_frontpage" name="progo_options[frontpage]" onchange="progo_frontpage_msg();"><?php
    foreach ( $choices as $k => $c ) {
		echo '<option value="'. $k .'"';
		if( $k == $options['frontpage'] ) {
			echo ' selected="selected"';
		}
		echo '>'. esc_attr($c) .'</option>';
	}
    ?></select><span class="description"><?php echo ( $msg != '' ? $msg : $msgs[$options['frontpage']] ); ?></span></p>
<script type="text/javascript">
function progo_frontpage_msg() {
	var msg = '';
	var sel = jQuery('#progo_frontpage');
	switch( sel.val() ) { <?php
	foreach ( $msgs as $k => $v ) {
		echo "case '$k':\n";
			echo "msg = '$v';\n";
			echo "break;";
	} ?>
	}
	sel.next().html(msg);
}
</script>
<?php }
endif;
if ( ! function_exists( 'progo_field_homeseconds' ) ):
/**
 * outputs HTML for Homepage "Cycle Seconds" field on Site Settings page
 * @since Ecommerce 1.0
 */
function progo_field_homeseconds() {
	$options = get_option( 'progo_options' );
	// check just in case show_on_front changed since this was last updated?
	// $options['frontpage'] = get_option('show_on_front');
	?><p><input id="progo_homeseconds" name="progo_options[homeseconds]" type="text" size="2" value="<?php echo absint($options['homeseconds']); ?>"><span class="description"> sec. per slide. Enter "0" to disable auto-rotation.</span></p>
<?php }
endif;
if ( ! function_exists( 'progo_section_text' ) ):
/**
 * (dummy) function called by 
 * add_settings_section( [id] , [title], 'progo_section_text', 'progo_site_settings' );
 * echos anchor link for that section
 * @since Ecommerce 1.0
 */
function progo_section_text( $args ) {
	echo '<a name="'. $args['id'] .'"></a>';
}
endif;
if ( ! function_exists( 'progo_bodyclasses' ) ):
/**
 * adds some additional classes to the <body> based on what page we're on
 * @param array of classes to add to the <body> tag
 * @since Ecommerce 1.0
 */
function progo_bodyclasses($classes) {
	switch ( get_post_type() ) {
		case 'wpsc-product':
			$classes[] = 'wpsc';
			break;
		case 'post':
			$classes[] = 'blog';
			break;
	}
	if ( is_front_page() ) {
		$options = get_option( 'progo_options' );
		if( $options['frontpage'] == 'featured' ) {
			$classes[] = 'wpsc';
		}
	}
	// add another class to body if we have a custom bg image
	if ( get_background_image() != '' ) {
		$classes[] = 'custombg';
	}
	
	return $classes;
}
endif;
if ( ! function_exists( 'progo_menuclasses' ) ):
/**
 * adds some additional classes to Menu Items
 * so we can mark active menu trails easier
 * @param array of classes to add to the <body> tag
 * @since Ecommerce 1.0
 */
function progo_menuclasses($items) {
	$blogID = get_option('progo_blog_id');
	foreach ( $items as $i ) {
		if ( $i->post_content == '[productspage]' && !is_front_page() ) {
			$i->classes[] = 'wpsc';
		}
		if ( $i->object_id == $blogID ) {
			$i->classes[] = 'blog';
		}
	}
	//wp_die('<pre>'.print_r($items,true) .'</pre>');
	return $items;
}
endif;

if ( ! function_exists( 'progo_ecommerce_reccheck' ) ):
/**
 * check wpsc settings dimensions for thumbnail (product_image) & product image (single_view_image)
 * @since Ecommerce 1.1.23
 */
function progo_ecommerce_reccheck( $echo ) {
	if ( get_option( 'product_image_width' ) == 70 && get_option( 'product_image_height' ) == 70 && get_option( 'single_view_image_width' ) == 290 && get_option( 'single_view_image_height' ) == '' ) {
		if ( $echo === true ) {
			$wpec = 'options-general.php?page=wpsc-settings&tab=';
			$tabs = array(
				"General",
				"Presentation",
				"Taxes",
				"Shipping",
				"Payment Gateway",
				"Checkout"
			);
			for ( $i = 0; $i < count($tabs); $i++ ) {
				$l = ($tabs[$i] == "Payment Gateway" ? "gateway" : strtolower($tabs[$i]) );
				echo ( $i > 0 ? ' &nbsp;|&nbsp; ' : '' ). '<a href="'. admin_url( $wpec . $l ) .'" target="_blank">'. $tabs[$i] .'</a>';
			}
		} else {
			return true;
		}
	} else {
		if ( $echo === true ) {
			echo "<p><strong>A few WP e-Commerce Store Settings, like Product Thumbnail Sizes, differ from ProGo Themes' Recommended Settings</strong></p><p>";
			echo '<a href="'.wp_nonce_url("admin.php?progo_admin_action=reset_wpsc", 'progo_reset_wpsc').'" class="button-primary">Click Here to Reset</a></p>';
		} else {
			return false;
		}
	}
}
endif;			
if ( ! function_exists( 'progo_ecommerce_completeness' ) ):
/**
 * check which step / % complete current site is at
 * @since Ecommerce 1.1.23
 */
function progo_ecommerce_completeness( $onstep ) {
	if ( $onstep < 1 || $onstep > 100 ) {
		$onstep = 1;
	}
	
	if ( $onstep < 21 ) { // ok check it
		switch($onstep) {
			case 1: // check API auth
				$apiauth = get_option( 'progo_ecommerce_apiauth', true );
				if( $apiauth == '100' ) {
					$onstep = 2;
				}
				break;
			case 2: // WP e-Commerce INSTALLED
				$plugs = get_plugins();
				if( isset( $plugs['wp-e-commerce/wp-shopping-cart.php'] ) == true ) {
					$onstep = 3;
				}
				break;
			case 3: // WP e-Commerce ACTIVATED
				if ( is_plugin_active( 'wp-e-commerce/wp-shopping-cart.php' ) ) {
					$onstep = 4;
				}
				break;
			case 4: // ProGo Recommended Settings
				if ( progo_ecommerce_reccheck(false) === true ) {
					$onstep = 5;
				}
				break;
			case 5: // WPEC Store Location
				$base_country = get_option( 'base_country', '' );
				if ( $base_country !=='' ) {
					$onstep = 6;
				}
				break;
			case 6: // WPEC Currency
				// now that base_country is set, if CURRENCY = 156 = NZ$ , & base_country != NZ, then needs adjusting
				$base_country = get_option( 'base_country', '' );
				$currency = absint( get_option( 'currency_type' ) );
				if ( ( $currency==156 && $base_country=='NZ' ) || ( $currency != 156 ) ) {
					$onstep = 7;
				}
				break;
			case 7: // WPEC Tax Settings
				$notaxes = get_option('progo_ecommerce_notaxes');
				$wpec_taxes_enabled = get_option('wpec_taxes_enabled');
				if ( $notaxes==true || ($notaxes==false && $wpec_taxes_enabled==1) ) {
					$onstep = 8;
				}
				break;
			case 8: // WPEC Shipping
				$noshipping = get_option('progo_ecommerce_noshipping');
				$wpec_noshipping = get_option('do_not_use_shipping');
				if ( $noshipping==true || ($noshipping==false && $wpec_noshipping!=1) ) {
					$onstep = 9;
				}
				break;
			case 9: // WPEC Payment Gateway
				$gateways = get_option('custom_gateway_options', true);
				$stilltest = false;
				foreach ( $gateways as $g ) {
					if ( $g == 'wpsc_merchant_testmode' ) {
						$stilltest = true;
					}
				}
				if ( $stilltest == false ) {
					$onstep = 10;
				}
				break;
			case 10: // Permalinks
				$permalink = get_option( 'permalink_structure', '' );
				$defaultok = get_option( 'progo_permalink_checked', false );
				if ( ( $permalink != '' ) || ( $permalink == '' && $defaultok == true ) ) {
					$onstep = 11;
				}
				break;
			case 11: // Product Category(s)
				$args = array(
					'hide_empty' => 0
				);
				$pcats = get_terms( 'wpsc_product_category', $args );
				if( ( count($pcats) != 1 ) || ( $pcats[0]->name != 'Product Category' ) ) {
					$onstep = 12;
				}
				break;
			case 12: // Products
				
				break;
			case 13: // Featured Products
				
				break;
			case 14: // Homepage Displays...
				
				break;
			case 15: // Homepage Slides
				
				break;
			case 16: // Main Menu
				
				break;
			case 17: // ABOUT page
				
				break;
			case 18: // TERMS page
				
				break;
			case 19: // PRIVACY page
				
				break;
			case 20: // CUSTOMER SUPPORT page
				
				break;
		}
	}
	return $onstep;
}
endif;
/**
 * hooked to 'admin_notices' by add_action in progo_setup()
 * used to display "Settings updated" message after Site Settings page has been saved
 * @uses get_option() To check if our Site Settings were just saved.
 * @uses update_option() To save the setting to only show the message once.
 * @since Ecommerce 1.0
 */
function progo_admin_notices() {
	// api auth check
	$apiauth = get_option( 'progo_ecommerce_apiauth', true );
	if( $apiauth != '100' ) {
	?>
	<div class="error">
		<p><?php
        switch($apiauth) {
			case 'new':	// key has not been entered yet
				echo '<a href="themes.php?page=progo_admin" title="Site Settings">Please enter your ProGo Themes API Key to Activate your theme.</a>';
				break;
			case '999': // invalid key?
				echo 'Your ProGo Themes API Key appears to be invalid. <a href="themes.php?page=progo_admin" title="Site Settings">Please double check it.</a>';
				break;
			case '300': // wrong site URL?
				echo '<a href="themes.php?page=progo_admin" title="Site Settings">The ProGo Themes API Key you entered</a> is already bound to another URL.';
				break;
		}
		?></p>
	</div>
<?php
	}
	
	if( get_option('progo_settings_just_saved')==true ) { ?>
	<div class="updated fade">
		<p>Settings updated. <a href="<?php bloginfo('url'); ?>/">View site</a></p>
	</div>
<?php
		update_option('progo_settings_just_saved',false);
	}
	
	$onstep = absint(get_option('progo_ecommerce_onstep', true));
	$onstep = progo_ecommerce_completeness( $onstep );
	update_option( 'progo_ecommerce_onstep', $onstep);
	
	/*
	echo '<div class="updated progo-steps">on step #'. $onstep .'</div>';
	if ( $onstep > 1 && $onstep < 21 ) {
		// couldnt check step 2 before but now we have get_plugins() function
		if ( ($onstep == 2) && ( $_REQUEST['action'] == 'install-plugin' ) ) {
				return;
		}
		
		echo '<div class="updated progo-steps">';
		$pct = 0;
		$nst = '';
		switch($onstep) {
			case 2: // WP e-Commerce INSTALLED
				$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url( 'update.php?action=install-plugin&amp;plugin=wp-e-commerce', 'install-plugin_wp-e-commerce' ) : 'plugin-install.php';
				$pct = 10;
				$nst = '<a href="'. esc_url( $lnk ) .'">Click Here to Install the WP e-Commerce Plugin</a>';
				break;
			case 3: // WP e-Commerce ACTIVATED
				$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url( 'plugins.php?action=activate&amp;plugin=wp-e-commerce/wp-shopping-cart.php', 'activate-plugin_wp-e-commerce/wp-shopping-cart.php' ) : 'plugins.php';
				$pct = 15;
				$nst = '<a href="'. esc_url( $lnk ) .'">Click Here to Activate the WP e-Commerce Plugin</a>';
				break;
			case 4: // ProGo Recommended Settings
				$pct = 20;
				$nst = 'A few WP e-Commerce Store Settings, like Product Thumbnail Sizes, differ from the Recommended Settings. <a href="'. wp_nonce_url("admin.php?progo_admin_action=reset_wpsc", 'progo_reset_wpsc') .'">Click Here to Reset</a>';
				break;
			case 5: // WPEC Store Location
				$pct = 22;
				$nst = '<a href="'. admin_url("options-general.php?page=wpsc-settings") .'">Set your Store\'s Base Country/Region</a>';
				break;
			case 6: // WPEC Currency
				$pct = 25;
				$nst = '<a href="'. admin_url("options-general.php?page=wpsc-settings") .'">Set your Store\'s Currency Settings</a>';
				break;
			case 7: // WPEC Tax Settings
				$pct = 27;
				$nst = '<a href="'. wp_nonce_url("admin.php?progo_admin_action=no_taxes", 'progo_no_taxes') .'">Click Here if your Store will NOT charge Taxes</a>. Otherwise, <a href="'. admin_url("options-general.php?page=wpsc-settings&tab=taxes") .'">configure Taxes here</a>.';
				break;
			case 8: // WPEC Shipping
				$pct = 32;
				$nst = '<a href="'. wp_nonce_url("admin.php?progo_admin_action=no_shipping", 'progo_no_shipping') .'">Click Here if your Store will NOT charge Shipping</a>. Otherwise, <a href="'. admin_url("options-general.php?page=wpsc-settings&tab=shipping") .'">configure Shipping here</a>.';
				break;
			case 9: // WPEC Payment Gateway
				$pct = 42;
				$nst = '<a href="'. admin_url("options-general.php?page=wpsc-settings&tab=gateway") .'">Please choose a Payment Gateway besides the Test Gateway</a>.';
				break;
			case 10: // Permalinks
				$pct = 52;
				$nst = 'Your <em>Permalinks</em> settings are still set to the Default option. <a href="'. wp_nonce_url("admin.php?progo_admin_action=permalink_recommended", 'progo_permalink_check') .'">Use the ProGo-Recommended "Day and name" setting</a>, <a href="'. admin_url("options-permalink.php") .'">Choose another non-Default option for yourself</a>, or <a href="'. wp_nonce_url("admin.php?progo_admin_action=permalink_default", 'progo_permalink_check') .'">keep the Default setting and move to the next step</a>.';
				break;
			case 11: // Product Category(s)
				$pct = 54;
				$args = array(
					'hide_empty' => 0
				);
				$pcats = get_terms( 'wpsc_product_category', $args );
				$nst = 'WP e-Commerce groups Products into Categories. <a href="'. admin_url('edit-tags.php?taxonomy=wpsc_product_category&post_type=wpsc-product') .'">Add another category</a>, or <a href="'. admin_url('edit-tags.php?action=edit&taxonomy=wpsc_product_category&tag_ID='. $pcats[0]->term_id .'&post_type=wpsc-product') .'">change the name of the first (default) category to something besides "Product Category"</a>.';
				//http://localhost/wp-admin/edit-tags.php?action=edit&taxonomy=wpsc_product_category&tag_ID=5&post_type=wpsc-product
				break;
			case 12: // Products
				$pct = 56;
				$nst = 'You are now ready to add Products to your Store! Click <a href="'. admin_url('post-new.php?post_type=wpsc-product') .'">Add New</a> under the left <a href="'. admin_url('edit.php?post_type=wpsc-product') .'">Products</a> menu.';
				break;
			case 13: // Featured Products
				$pct = 66;
				$nst = 'Featured Products';
				break;
			case 14: // Homepage Displays...
				$pct = 70;
				$nst = 'Homepage Displays...';
				break;
			case 15: // Homepage Slides
				$pct = 74;
				$nst = 'Homepage Slides';
				break;
			case 16: // Main Menu
				$pct = 78;
				$nst = 'Main Menu';
				break;
			case 17: // ABOUT page
				$pct = 88;
				$nst = 'ABOUT page';
				break;
			case 18: // TERMS page
				$pct = 91;
				$nst = 'TERMS page';
				break;
			case 19: // PRIVACY page
				$pct = 94;
				$nst = 'PRIVACY page';
				break;
			case 20: // CUSTOMER SUPPORT page
				$pct = 97;
				$nst = 'CUSTOMER SUPPORT page';
				break;
			default: // #1- API key
				
				break;
		}
		echo '<p>Your ProGo Ecommerce site is <strong>'. $pct .'% Complete</strong> - Next Step: '. $nst .'</p></div>';
	}
	*/
}

/**
 * hooked to 'site_transient_update_themes' by add_filter in progo_setup()
 * checks ProGo-specific URL to see if our theme is up to date!
 * @param array of checked Themes
 * @uses get_allowed_themes() To retrieve list of all installed themes.
 * @uses wp_remote_post() To check remote URL for updates.
 * @return checked data array
 * @since Ecommerce 1.0
 */
function progo_update_check($data) {
	if ( is_admin() == false ) {
		return $data;
	}
	
	$themes = get_allowed_themes();
	
	if ( isset( $data->checked ) == false ) {
		$checked = array();
		// fill CHECKED array - not sure if this is necessary for all but doesnt take a long time?
		foreach ( $themes as $thm ) {
			// we don't care to check CHILD themes
			if( $thm['Parent Theme'] == '') {
				$checked[$thm[Template]] = $thm[Version];
			}
		}
		$data->checked = $checked;
	}
	if ( isset( $data->response ) == false ) {
		$data->response = array();
	}
	
	$request = array(
		'slug' => "ecommerce",
		'version' => $data->checked[ecommerce],
		'siteurl' => get_bloginfo('url')
	);
	
	// Start checking for an update
	global $wp_version;
	$apikey = get_option('progo_ecommerce_apikey',true);
	if ( $apikey != '' ) {
		$apikey = substr( strtolower( str_replace( '-', '', $apikey ) ), 0, 32);
	}
	$checkplz = array(
		'body' => array(
			'action' => 'theme_update', 
			'request' => serialize($request),
			'api-key' => $apikey
		),
		'user-agent' => 'WordPress/'. $wp_version .'; '. get_bloginfo('url')
	);

	$raw_response = wp_remote_post('http://www.progo.com/updatecheck/', $checkplz);
	
	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
		$response = unserialize($raw_response['body']);
		
	if ( !empty( $response ) ) {
		// got response back. check authcode
		// wp_die('response:<br /><pre>'. print_r($response,true) .'</pre><br /><br />apikey: '. $apikey );
		// only save AUTHCODE if APIKEY is not blank.
		if ( $apikey != '' ) {
			update_option( 'progo_ecommerce_apiauth', $response[authcode] );
		} else {
			update_option( 'progo_ecommerce_apiauth', 'new' );
		}
		if ( version_compare($data->checked[ecommerce], $response[new_version], '<') ) {
			$data->response[ecommerce] = array(
				'new_version' => $response[new_version],
				'url' => $response[url],
				'package' => $response[package]
			);
		}
	}
	
	return $data;
}

function progo_to_twentyten() {
	$msg = 'This ProGo Themes site is currently not Activated.';
	
	if(current_user_can('edit_pages')) {
		$msg .= '<br /><br /><a href="'. trailingslashit(get_bloginfo('url')) .'wp-admin/themes.php?page=progo_admin">Click here to update your API Key</a>';
	}
	wp_die($msg);
}

if ( ! function_exists( 'progo_product_image_forms' ) ):
/**
 * html for WPSC product images meta box
 * @since Ecommerce 1.0
 */
function progo_product_image_forms() {

    global $post;
    
    edit_multiple_image_gallery( $post );

	$tab = has_post_thumbnail($post->ID) ? 'gallery' : 'type';
    ?>
    <p><strong <?php if ( isset( $display ) ) echo $display; ?>><a href="media-upload.php?parent_page=wpsc-edit-products&post_id=<?php echo $post->ID; ?>&type=image&tab=<?php echo esc_attr($tab); ?>&TB_iframe=1&width=640&height=566" class="thickbox" title="Manage Your Product Images"><?php _e( 'Manage Product Images', 'wpsc' ); ?></a></strong></p>
<?php
}
endif;
/**
 * hooked by add_filter to 'wp_before_admin_bar_render'
 * to tweak the new WP 3.1 ADMIN BAR
 * @since Ecommerce 1.0
 */
function progo_admin_bar_render() {
	global $wp_admin_bar;
	
	$wp_admin_bar->remove_menu('widgets');
	$wp_admin_bar->add_menu( array( 'id' => 'appearance', 'title' => __('Appearance'), 'href' => admin_url('themes.php?page=progo_admin') ) );
	// move Appearance > Widgets & Menus submenus to below our new ones
	$wp_admin_bar->remove_menu('widgets');
	$wp_admin_bar->remove_menu('menus');
	$wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'homeslides', 'title' => __('Homepage Slides'), 'href' => admin_url('themes.php?page=progo_home_slides') ) );
	$wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'background', 'title' => __('Background'), 'href' => admin_url('themes.php?page=custom-background') ) );
	$wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'menus', 'title' => __('Menus'), 'href' => admin_url('nav-menus.php') ) );
	$wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'widgets', 'title' => __('Widgets'), 'href' => admin_url('widgets.php') ) );
	
	$avail = progo_colorschemes();
	if ( count($avail) > 0 ) {
		$wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'progo_colorscheme', 'title' => 'Color Scheme', 'href' => admin_url('themes.php?page=progo_admin') ) );
	}
	foreach($avail as $color) {
		$wp_admin_bar->add_menu( array( 'parent' => 'progo_colorscheme', 'id' => 'progo_colorscheme'.esc_attr($color), 'title' => esc_attr($color), 'href' => admin_url('admin.php?progo_admin_action=color'. esc_attr($color) ) ) );
	}
}

if(!function_exists('progo_mail_content_type')):
function progo_mail_content_type( $content_type ) {
	return 'text/html';
}
endif;

if(!function_exists('progo_nomenu_cb')):
function progo_nomenu_cb() {
	return '<ul></ul>';
}
endif;