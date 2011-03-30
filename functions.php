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
 * progo_admin_menu_cleanup, progo_welcome, progo_custom_login_logo, progo_custom_login_url, progo_metabox_cleanup, progo_colorschemes ...
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
	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style( 'css/editor-style.css' );
	
	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'mainmenu' => 'Main Menu',
		'footer' => 'Footer Links'
	) );
	
	// This theme uses post thumbnails
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'large', 596, 397, true );
	add_image_size( 'prodL', 290, 290, true );
	add_image_size( 'prod3', 190, 190, true );
	add_image_size( 'thm', 70, 70, true );
	
	// add custom actions
	add_action( 'admin_init', 'progo_admin_init' );
	add_action( 'widgets_init', 'progo_ecommerce_widgets' );
	add_action( 'admin_menu', 'progo_admin_menu_cleanup' );
	add_action( 'login_head', 'progo_custom_login_logo' );
	add_action( 'login_headerurl', 'progo_custom_login_url' );
	add_action('wp_print_scripts', 'progo_add_scripts');
	add_action('wp_print_styles', 'progo_add_styles');
	add_action( 'admin_notices', 'progo_admin_notices' );
	add_action( 'wp_before_admin_bar_render', 'progo_admin_bar_render' );
	
	// add custom filters
	add_filter( 'site_transient_update_themes', 'progo_update_check' );
	add_filter( 'wpsc_pre_transaction_results', 'progo_prepare_transaction_results' );
	add_filter( 'wp_mail_content_type', 'progo_mail_content_type' );
	
	if ( !is_admin() ) {
		// brick it if not activated
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
		echo '<table id="logo"><tr><td><img src="'. esc_attr( $imagepath ) .'" alt="'. esc_attr( get_bloginfo( 'name' ) ) .'" /></td></tr></table>';
	} else {
		echo '<div id="logo">'. esc_html( get_bloginfo( 'name' ) ) .'<span class="g"></span></div>';
	}
}
endif;
if ( ! function_exists( 'progo_posted_on' ) ):
/**
 * Prints HTML with meta information for the current post—date/time and author.
 * @since ProGo Ecommerce 1.0
 */
function progo_posted_on() {
	printf( __( '<span class="%1$s">Posted on</span> %2$s <span class="meta-sep">by</span> %3$s', 'progo' ),
		'meta-prep meta-prep-author',
		sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
			get_permalink(),
			esc_attr( get_the_time() ),
			get_the_date()
		),
		sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
			get_author_posts_url( get_the_author_meta( 'ID' ) ),
			sprintf( esc_attr__( 'View all posts by %s', 'progo' ), get_the_author() ),
			get_the_author()
		)
	);
}
endif;
if ( ! function_exists( 'progo_posted_in' ) ):
/**
 * Prints HTML with meta information for the current post (category, tags and permalink).
 * @since ProGo Ecommerce 1.0
 */
function progo_posted_in() {
	// Retrieves tag list of current post, separated by commas.
	$tag_list = get_the_tag_list( '', ', ' );
	if ( $tag_list ) {
		$posted_in = __( 'This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'progo' );
	} elseif ( is_object_in_taxonomy( get_post_type(), 'category' ) ) {
		$posted_in = __( 'This entry was posted in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'progo' );
	} else {
		$posted_in = __( 'Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'progo' );
	}
	// Prints the string, replacing the placeholders.
	printf(
		$posted_in,
		get_the_category_list( ', ' ),
		$tag_list,
		get_permalink(),
		the_title_attribute( 'echo=0' )
	);
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
/********* Back-End Functions *********/
if ( ! function_exists( 'progo_admin_menu_cleanup' ) ):
/**
 * hooked to 'admin_menu' by add_action in progo_setup()
 * @since Ecommerce 1.0
 */
function progo_admin_menu_cleanup() {
	global $menu;
	global $submenu;
	// move POSTS to farther down the line...
	$menu[7] = $menu[5];
	
	add_menu_page( 'Welcome', 'ProGo Themes', 'edit_theme_options', 'progo_welcome', 'progo_welcome', get_bloginfo( 'template_url' ) .'/images/logo_menu.png', 5 );
	add_submenu_page( 'progo_welcome', 'Welcome', 'Welcome', 'edit_theme_options', 'progo_welcome', 'progo_welcome' );
	add_submenu_page( 'progo_welcome', 'Site Settings', 'Site Settings', 'edit_theme_options', 'progo_site_settings', 'progo_site_settings_page' );
	add_submenu_page( 'progo_welcome', 'Store Settings', 'Store Settings', 'edit_theme_options', 'wpsc-settings', 'options-general.php' );
	//add_submenu_page( 'progo_welcome', __( 'Store Sales', 'wpsc' ), __( 'Store Sales', 'wpsc' ), 'administrator', 'wpsc-sales-logs', 'wpsc_display_sales_logs' );
	//add_submenu_page( 'progo_welcome', __( 'Store Upgrades', 'wpsc' ), __( 'Store Upgrades', 'wpsc' ), 'administrator', 'wpsc-upgrades', 'wpsc_display_upgrades_page' );
	add_submenu_page( 'progo_welcome', 'Menus', 'Menus', 'edit_theme_options', 'nav-menus.php' );
	add_submenu_page( 'progo_welcome', 'Widgets', 'Widgets', 'edit_theme_options', 'widgets.php' );
	/*
	// and remove STORE SALES and STORE UPGRADES from DASHBOARD menu?
	if ( isset( $submenu['index.php'] ) ) {
		foreach ( $submenu['index.php'] as $ind => $sub ) {
			// sub[0] could change language so check the callback fn instead
			if ( in_array( $sub[2], array( 'wpsc-sales-logs', 'wpsc-ugrades' ) ) ) {
				unset($submenu['index.php'][$ind]);
			}
		}
	}
	*/
	// add an extra dividing line...
	$menu[6] = $menu[4];
	/*
	// and lets move MEDIA to after PRODUCTS
	$menu[50] = $menu[10];
	unset($menu[10]);
	// and lets move PRODUCTS to before PAGES
	$menu[10] = $menu[26];
	unset($menu[26]);
	*/
	//wp_die('<pre>'. print_r($menu,true) .'</pre>');
}
endif;
if ( ! function_exists( 'progo_welcome' ) ):
/**
 * ProGo Themes' Ecommerce WELCOME page function
 * from add_menu_page( 'Welcome', 'ProGo Themes', 'edit_theme_options', 'progo_welcome'...
 * in progo_admin_menu_cleanup()
 * @since Ecommerce 1.0
 */
function progo_welcome() {
	//must check that the user has the required capability 
	if ( current_user_can('edit_theme_options') == false) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	} ?>
	<div class="wrap">
    <div class="icon32" id="icon-themes"><br /></div><h2>Welcome</h2>
    <div id="dashboard-widgets-wrap">
    <div id="dashboard-widgets" class="metabox-holder">
    	<div class="postbox-container" style="width:64%">
        	<div id="progo_welcome" class="postbox">
            	<h3 class="hndle"><span>Welcome</span></h3>
                <div class="inside">
                    <p><img src="<?php bloginfo( 'template_url' ); ?>/images/logo_progo.png" style="float:right; margin: 0 0 21px 21px" alt="ProGo Themes" />ProGo Themes are easy and quick to set up using our step-by-step process below. <a href="http://www.progo.com/ProGo_QuickStartGuide.pdf" target="_blank">Click here to download the ProGo Themes Quick Start Guide (PDF)</a></p>
                    <?php progo_dashboard_widget_function(); ?>
                </div>
            </div>
         </div>
         <div class="postbox-container" style="width:35%">
        	<div id="progo_plugs" class="postbox">
            	<h3 class="hndle"><span>Recommended Plugins</span></h3>
                <div class="inside">
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
                </div>
            </div>
        </div>
    </div>
    <div class="clear"></div>
    </div>
	<?php
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
if ( ! function_exists( 'progo_admin_page_styles' ) ):
/**
 * hooked to 'admin_print_styles' by add_action in progo_setup()
 * adds thickbox js for WELCOME screen styling
 * @since Ecommerce 1.0
 */
function progo_admin_page_styles() {
	global $pagenow;
	if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) ) {
		$thispage = $_GET['page'];
		if ( $thispage == 'progo_welcome' ) {
			wp_enqueue_style( 'dashboard' );
			wp_enqueue_style( 'global' );
			wp_enqueue_style( 'wp-admin' );
			wp_enqueue_style( 'thickbox' );
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
	if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'progo_welcome' ) ) ) {
		wp_enqueue_script( 'thickbox' );
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
	if ( isset( $_REQUEST['progo_admin_action'] ) ) {
		switch( $_REQUEST['progo_admin_action'] ) {
			case 'reset_wpsc':
				progo_reset_wpsc(true);
				break;
			case 'reset_logo':
				progo_reset_logo();
				break;
			case 'make_featured':
				progo_featured_cat();
				break;
			case 'colorBlackGrey':
				progo_colorscheme_switch( 'BlackGrey' );
				break;
		}
	}
	
	// ACTION hooks
	add_action( 'admin_print_styles', 'progo_admin_page_styles' );
	add_action( 'admin_print_scripts', 'progo_admin_page_scripts' );
	
	// Site Settings page
	register_setting( 'progo_options', 'progo_options', 'progo_options_validate' );
	
	add_settings_section( 'progo_api', 'ProGo Themes API Key', 'progo_section_text', 'progo_site_settings' );
	add_settings_field( 'progo_api_key', 'API Key', 'progo_field_apikey', 'progo_site_settings', 'progo_api' );
	
	add_settings_section( 'progo_theme', 'Theme Customization', 'progo_section_text', 'progo_site_settings' );
	add_settings_field( 'progo_colorscheme', 'Color Scheme', 'progo_field_color', 'progo_site_settings', 'progo_theme' );
	add_settings_field( 'progo_logo', 'Logo', 'progo_field_logo', 'progo_site_settings', 'progo_theme' );

	add_settings_section( 'progo_info', 'Site Info', 'progo_section_text', 'progo_site_settings' );
	add_settings_field( 'progo_blogname', 'Site Name', 'progo_field_blogname', 'progo_site_settings', 'progo_info' );
	add_settings_field( 'progo_blogdescription', 'Slogan', 'progo_field_blogdesc', 'progo_site_settings', 'progo_info' );
	add_settings_field( 'progo_showdesc', 'Show/Hide Slogan', 'progo_field_showdesc', 'progo_site_settings', 'progo_info' );
	add_settings_field( 'progo_support', 'Customer Support', 'progo_field_support', 'progo_site_settings', 'progo_info' );
	add_settings_field( 'progo_copyright', 'Copyright Notice', 'progo_field_copyright', 'progo_site_settings', 'progo_info' );
	add_settings_field( 'progo_secure', 'Security Logos', 'progo_field_cred', 'progo_site_settings', 'progo_info' );
	add_settings_field( 'progo_companyinfo', 'Company Info', 'progo_field_compinf', 'progo_site_settings', 'progo_info' );

	add_settings_section( 'progo_homepage', 'Homepage', 'progo_section_text', 'progo_site_settings' );
	add_settings_field( 'progo_frontpage', 'Homepage Displays', 'progo_field_frontpage', 'progo_site_settings', 'progo_homepage' );
	
	// since there does not seem to be an actual THEME_ACTIVATION hook, we'll fake it here
	if ( get_option( 'progo_ecommerce_installed' ) != true ) {
		// also want to create a few other pages (Terms & Conditions, Privacy Policy), set up the FOOTER menu, and add these pages to it...
		
		$post_date = date( "Y-m-d H:i:s" );
		$post_date_gmt = gmdate( "Y-m-d H:i:s" );
		
		// create new menus in the Menu system
		$new_menus = array(
			'mainmenu' => 'Main Menu',
			'footer' => 'Footer Links'
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
		$new_pages = array(
			'home' => array(
				'title' => __( 'Home', 'progo' ),
				'content' => "This is your Home page",
				'id' => '',
				'menu' => 'mainmenu'
			),
			'about' => array(
				'title' => __( 'About', 'progo' ),
				'content' => "This Page could have info about your site/store",
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
				'content' => "List your Terms and Conditions here",
				'id' => '',
				'menu' => 'footer'
			),
			'privacy' => array(
				'title' => __( 'Privacy Policy', 'progo' ),
				'content' => "Put your Privacy Policy here",
				'id' => '',
				'menu' => 'footer'
			),
			'customer-service' => array(
				'title' => __( 'Customer Service', 'progo' ),
				'content' => "This Page could have Customer Service info on it",
				'id' => '',
				'menu' => 'footer'
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
		wp_redirect( get_option( 'siteurl' ) . '/wp-admin/admin.php?page=progo_site_settings' );
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
		'description' => 'Main Sidebar for most of your site\'s pages',
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
		'description' => 'The CHECKOUT page could be even more streamlined',
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
		'id' => 'footer',
		'description' => 'The Footer area has room for widgets as well',
		'before_widget' => '<div class="fblock %1$s %2$s">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="title"><span class="spacer">',
		'after_title' => '</span></h3><div class="inside">'
	));
	
	$progo_widgets = array( 'FBLikeBox', 'Tweets', 'Share', 'Social', 'Support' );
	foreach ( $progo_widgets as $w ) {
		require_once( 'widgets/widget-'. strtolower($w) .'.php' );
		register_widget( 'ProGo_Widget_'. $w );
	}
}
endif;
if ( ! function_exists( 'progo_dashboard_widget_function' ) ):
/**
 * prints WELCOME steps
 * @since Ecommerce 1.0
 */
function progo_dashboard_widget_function() {
	$options = get_option( 'progo_options' );
	// Display whatever it is you want to show
	$greyout = ' style="color: #999;"';
	echo '<ol>';
	$lnk = '<a href="admin.php?page=progo_site_settings" title="Site Settings">';
	echo '<li>Update your custom '. $lnk .'Site Settings</a> under <strong>ProGo Themes</strong> in the left menu. Customize...';
	echo '<ul><li>Your Site\'s Name : <a href="admin.php?page=progo_site_settings#progo_info">'. get_option( 'blogname' ) .'</a></li><li>Color Scheme : <a href="admin.php?page=progo_site_settings#progo_theme">'. esc_html( $options['colorscheme'] ) .'</a></li></ul></li>';
	echo "<li>Your <em>Ecommerce</em> ProGo Theme works hand-in-hand with the <strong>WP e-Commerce</strong> Plugin. ";
	// check for wp-e-commerce installed..
	$plugs = get_plugins();
	$goon = isset( $plugs['wp-e-commerce/wp-shopping-cart.php'] );
	if( $goon ) {
		echo "<br />Congratulations! WP e-Commerce appears to be installed.";
	} else {
		$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url( 'update.php?action=install-plugin&amp;plugin=wp-e-commerce', 'install-plugin_wp-e-commerce' ) : 'plugin-install.php';
		echo '<br /><strong><a href="'. esc_url( $lnk ) .'" onclick="alert(\'since the STABLE release is v3.7.8, and we are working with v3.8, we need to wait on this one-click...\'); return false;">Click Here to install the WP e-Commerce Plugin.</a></strong>';
		$goon = false;
	}
	echo '</li>';
	//is WP e-Commerce Plugin activated?
	echo '<li'. ($goon ? '' : $greyout) .'>';
	if ( !$goon) {
		echo 'Is WP e-Commerce Plugin activated?';
	} else {
		if ( function_exists('wpsc_admin_pages')) {
			echo "WP e-Commerce Plugin is activated!<ul>";
			
			//check wpsc settings dimensions for thumbnail (product_image) & product image (single_view_image)
			if ( get_option( 'product_image_width' ) == 70 && get_option( 'product_image_height' ) == 70 && get_option( 'single_view_image_width' ) == 300 && get_option( 'single_view_image_height' ) == 300 ) {
				echo "<li>WP e-Commerce settings match ProGo Themes' Recommended Settings!</li>";
			} else {
				echo "<li>A few WP e-Commerce Store Settings, like Product Thumbnail Sizes, differ from ProGo Themes' Recommended Settings : ";
				echo '<a href="'.wp_nonce_url("admin.php?progo_admin_action=reset_wpsc", 'progo_reset_wpsc').'">Click Here to Reset</a></li>';
			}
			
        	echo '<li>Edit your WP e-Commerce store\'s <a href="'. wp_nonce_url( 'options-general.php?page=wpsc-settings', 'tab-general' ) .'">General Settings</a></li>';
        	echo '<li>Payment Method is set to ';
			$cur_gateway = get_option( 'custom_gateway_options' );
			global $nzshpcrt_gateways;
			foreach( $nzshpcrt_gateways as $gateway ) {
				if ( $gateway['class_name'] == $cur_gateway[0] ) {
					echo esc_html( $gateway['name'] );
				}
			}
			echo ' : <a href="'. wp_nonce_url( 'options-general.php?page=wpsc-settings&tab=gateway', 'tab-gateway' ) .'">Edit Payment Settings</a></li>';
			echo '<li>Shipping is currently ';
			echo ( absint( get_option( 'do_not_use_shipping' ) ) == 1 ? 'Dis' : 'En');
			echo 'abled : <a href="'. wp_nonce_url( 'options-general.php?page=wpsc-settings&tab=shipping', 'tab-shipping' ) .'">Edit Shipping Settings</a></li>';
			echo '</ul>';
        } else {
			$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url('plugins.php?action=activate&amp;plugin=wp-e-commerce/wp-shopping-cart.php&amp;plugin_status=all&amp;paged=1', 'activate-plugin_wp-e-commerce/wp-shopping-cart.php') : 'plugins.php';
			echo '<a href="'. esc_url($lnk) .'">Click Here to activate the WP e-Commerce Plugin</a>';
			$goon = false;
		}
	}
	echo '</li>';
	// check for Featured cat ?
	if ( $goon ) {
		echo '<li>';
		$fcat = get_option( 'progo_ecommerce_fcat' );
		if ( $fcat !== false ) {
			echo 'Your <a href="edit.php?wpsc_product_category=featured&post_type=wpsc-product">Featured Products</a> Category is all set!';
		} else {
			$pcats = get_terms( 'wpsc_product_category', array( 'hide_empty' => 0 ) );
			foreach ( $pcats as $c ) {
				if ( $c->slug == 'featured' ) {
					$fcat = $c->term_id;
				}
			}
			
			if ( $fcat !== false ) {
				echo 'Your <a href="edit.php?wpsc_product_category=featured&post_type=wpsc-product">Featured Products</a> Category is all set!';
			} else {
				echo 'Your Home Page can display "Featured" Products. <a href="'.wp_nonce_url("admin.php?progo_admin_action=make_featured", 'progo_make_featured').'">Click Here to Create a "Featured Products" Category</a>.';
			}
		/*
		$num = $num->publish;
		if ( $num > 0 ) {
			$lnk = '<a href="edit.php?post_type=wpsc-product">';
			echo 'You have '. $lnk . absint( $num ) .' Product'. ( $num > 1 ? 's' : '' ) .'</a> set up in WP e-Commerce. ';
			echo '<ul><li>'. $lnk .'Edit Product'. ( $num > 1 ? 's' : '' ) .'</a></li>';
			echo '<li><a href="post-new.php?post_type=wpsc-product">Add Another Product</a></li></ul>';
		} else {
			$goon = false;
			echo '<a href="post-new.php?post_type=wpsc-product">Create your first Product.</a>';
		}
		*/
		}
	} else {
		echo '<li'. $greyout .'>"Featured" Products Category?';
	}
	echo '</li>';
	
	
	// check # of Products (at least 1?)
	if ( $goon ) {
		echo '<li>';
		$num = wp_count_posts( 'wpsc-product' );
		$num = $num->publish;
		if ( $num > 0 ) {
			$lnk = '<a href="edit.php?post_type=wpsc-product">';
			echo 'You have '. $lnk . absint( $num ) .' Product'. ( $num > 1 ? 's' : '' ) .'</a> set up in WP e-Commerce. ';
			echo '<ul><li>'. $lnk .'Edit Product'. ( $num > 1 ? 's' : '' ) .'</a></li>';
			echo '<li><a href="post-new.php?post_type=wpsc-product">Add Another Product</a></li></ul>';
		} else {
			$goon = false;
			echo '<a href="post-new.php?post_type=wpsc-product">Create your first Product.</a>';
		}
	} else {
		echo '<li'. $greyout .'>Have you created at least 1 Product?';
	}
	echo '</li>';
	
	echo '</ol>';
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
	}
}
endif;
add_action( 'do_meta_boxes', 'progo_metabox_cleanup' );

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
	}
}
endif;

if ( ! function_exists( 'progo_colorschemes' ) ):
/**
 * @return array of Color Schemes
 * @since Ecommerce 1.0
 */
function progo_colorschemes() {
	return array( 'BlackGrey' );
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
	update_option( 'single_view_image_width', 300 );
	update_option( 'single_view_image_height', 300 );
	
	update_option( 'wpsc_email_receipt', "Any items to be shipped will be processed as soon as possible, any items that can be downloaded can be downloaded using the links on this page. All prices include tax and postage and packaging where applicable.\n\n%product_list%%total_price%%find_us%" );
	
	if ( $fromlink == true ) {
		wp_redirect( get_option('siteurl') .'/wp-admin/admin.php?page=progo_welcome' );
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
	
	wp_redirect( get_option('siteurl') .'/wp-admin/admin.php?page=progo_site_settings' );
	exit();
}
endif;
if ( ! function_exists( 'progo_featured_cat' ) ):
/**
 * helper function to create the Featured Products category
 * @since Ecommerce 1.0
 */
function progo_featured_cat() {
	check_admin_referer( 'progo_make_featured' );
	
	$aok = true;
	$fcat = get_option( 'progo_ecommerce_fcat' );
	if ( $fcat !== false ) {
		$aok = false;
	} else {
		// create a new category
		$new_term = wp_insert_term(
			'Featured Products',
			'wpsc_product_category',
			array(
				'slug' => 'featured',
				'parent' => 0
			)
		);
		
		if( is_array($new_term) == false ) {
			$aok = false;
		}
	}
	
	if( $aok ) {
		update_option( 'progo_ecommerce_fcat', $new_term['term_id'] );
	} else {
		wp_die('Something went wrong in creating the Featured Products category');
	}
	
	wp_redirect( get_option('siteurl') .'/wp-admin/admin.php?page=progo_welcome' );
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
			"frontpage" => ""
		);
		$def["frontpage"] = get_option('show_on_front');		
		
		update_option( 'progo_options', $def );
	}
	
	update_option( 'progo_ecommerce_installed', true );
	update_option( 'progo_ecommerce_apikey', '' );
	update_option( 'progo_ecommerce_apiauth', 'new' );
	
	update_option( 'wpsc_ignore_theme', true );
	
	// set large image size
	update_option( 'large_size_w', 650 );
	update_option( 'large_size_h', 413 );
	
	progo_reset_wpsc();
}

if ( ! function_exists( 'progo_options_validate' ) ):
/**
 * ProGo Site Settings Options validation function
 * from register_setting( 'progo_options', 'progo_options', 'progo_options_validate' );
 * in progo_admin_init()
 * also handles uploading of custom Site Logo
 * @param $input options to validate
 * @return $input after validation has taken place
 * @since Ecommerce 1.0
 */
function progo_options_validate( $input ) {
	// do validation here...
	$arr = array( 'blogname', 'blogdescription', 'colorscheme', 'support', 'copyright', 'apikey', 'companyinfo' );
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
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', get_option('progo_homepage_id') );
			update_post_meta( get_option('progo_homepage_id'), '_wp_page_template', 'page-featured.php' );
		case 'page':
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', get_option('progo_homepage_id') );
			delete_post_meta( get_option('progo_homepage_id'), '_wp_page_template' );
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
	
	// store API KEY in its own option
	if ( $input['apikey'] != get_option( 'progo_ecommerce_apikey' ) ) {
		update_option( 'progo_ecommerce_apikey', substr( $input['apikey'], 0, 39 ) );
	}
	unset( $input['apikey'] );
	
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
			echo ' <img src="'. get_bloginfo('template_url') .'/images/check.jpg" alt="aok" class="kcheck" />';
			break;
		default:
			echo ' <img src="'. get_bloginfo('template_url') .'/images/x.jpg" alt="X" class="kcheck" title="'. $apiauth .'" />';
			break;
	}
	echo '<br /><span class="description">You API Key was sent via email when you purchased the Ecommerce theme from ProGo Themes.</span>';
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
 * outputs HTML for "Homepage" field on Site Settings page
 * @since Ecommerce 1.0
 */
function progo_field_frontpage() {
	// Latest Blog Posts, (Featured Products), Static Content
	$choices = array(
		'posts' => 'Latest Blog Posts',
		'featured' => 'Featured Products',
		'page' => 'Static Content'
	);
	$msg = '';
	
	$fcat = get_option( 'progo_ecommerce_fcat' );
	if ( $fcat == false ) {
		unset($choices['featured']);
		$msg = '<br />Your Home Page can display "Featured" Products';
		
		if ( function_exists('wpsc_admin_pages')) {
			$msg .= '. <a href="'.wp_nonce_url("admin.php?progo_admin_action=make_featured", 'progo_make_featured').'">Click Here to Create a "Featured Products" Category</a>.';
		} else {
			$msg .= ', but WP E-Commerce Plugin appears to be inactive. ';
			$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url('plugins.php?action=activate&amp;plugin=wp-e-commerce/wp-shopping-cart.php&amp;plugin_status=all&amp;paged=1', 'activate-plugin_wp-e-commerce/wp-shopping-cart.php') : 'plugins.php';
			$msg .= '<a href="'. esc_url($lnk) .'">Click Here to Activate</a>';
		}
	}
	
//	$msg .= '<pre>'. print_r(get_option('show_on_front'),true)  .'</pre>'. print_r(get_option('page_on_front'),true) .'</pre>'. print_r(get_option('page_for_posts'),true) .'</pre>';
	
	$options = get_option( 'progo_options' );
	// check just in case show_on_front changed since this was last updated?
	// $options['frontpage'] = get_option('show_on_front');
	
	?><select id="progo_frontpage" name="progo_options[frontpage]"><?php
    foreach ( $choices as $k => $c ) {
		echo '<option value="'. $k .'"';
		if( $k == $options['frontpage'] ) {
			echo ' selected="selected"';
		}
		echo '>'. esc_attr($c) .'</option>';
	}
    ?></select><span class="description"><?php echo $msg; ?></span>
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
	<div id="message" class="error">
		<p><?php
        switch($apiauth) {
			case 'new':	// key has not been entered yet
				echo '<a href="admin.php?page=progo_site_settings" title="Site Settings">Please enter your ProGo Themes API Key to Activate your theme.</a>';
				break;
			case '999': // invalid key?
				echo 'Your ProGo Themes API Key appears to be invalid. <a href="admin.php?page=progo_site_settings" title="Site Settings">Please double check it.</a>';
				break;
			case '300': // wrong site URL?
				echo '<a href="admin.php?page=progo_site_settings" title="Site Settings">The ProGo Themes API Key you entered</a> is already bound to another URL.';
				break;
		}
		?></p>
	</div>
<?php
	}
	
	if( get_option('progo_settings_just_saved')==true ) {
	?>
	<div id="message" class="updated fade">
		<p>Settings updated. <a href="<?php bloginfo('url'); ?>/">View site</a></p>
	</div>
<?php
		update_option('progo_settings_just_saved',false);
	}
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
		'version' => $data->checked[direct],
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
		//wp_die('response:<br /><pre>'. print_r($response,true) .'</pre><br /><br />apikey: '. $apikey );
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
		$msg .= '<br /><br /><a href="'. trailingslashit(get_bloginfo('url')) .'wp-admin/admin.php?page=progo_site_settings">Click here to update your API Key</a>';
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
	// since we are hiding COMMENTING and POSTS right now...
	
	// add links to ProGo Ecommerce pages
	$wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'progo_settings', 'title' => __('Site Settings'), 'href' => admin_url('admin.php?page=progo_site_settings') ) );
	$wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'store_settings', 'title' => __('Store Settings'), 'href' => admin_url('admin.php?page=wpsc-settings') ) );
	
	$avail = progo_colorschemes();
	if ( count($avail) > 0 ) {
		$wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'progo_colorscheme', 'title' => 'Color Scheme', 'href' => admin_url('admin.php?page=progo_site_settings') ) );
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