<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package ProGo
 * @subpackage Ecommerce
 * @since Ecommerce 1.0
 */
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
<title><?php if ( wp_title( '', false ) ) { wp_title( '' ); } else { echo get_bloginfo( 'name' ) .' | '. get_bloginfo( 'description' ); } ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>><div id="fx">
<div id="wrap" class="container_12">
	<div id="page" class="container_12">
        <div id="hdr" class="container_12">
        	<div class="grid_6">
            <?php progo_sitelogo();
            $options = get_option( 'progo_options' );
			$showdesc = true;
			if ( isset( $options['showdesc'] ) ) {
				$showdesc = ( (int) $options['showdesc'] == 1 );
			}
            if ( $showdesc === true ) { ?>
            <div id="slogan"><?php bloginfo( 'description' ); ?></div>
            <?php } ?>
            </div>
            <?php get_sidebar('header');
			wp_nav_menu( array( 'container' => 'false', 'theme_location' => 'mainmenu', 'menu_id' => 'nav', 'fallback_cb' => 'progo_nav_fallback' ) ); ?>
        </div>
