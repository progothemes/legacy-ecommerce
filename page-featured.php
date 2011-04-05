<?php
/**
 * Template Name: Featured Products
 *
 * Featured Products page template
 *
 * @package ProGo
 * @subpackage Ecommerce
 * @since Ecommerce 1.0
 */

get_header();
?>
<div id="container" class="container_12">
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
<div id="pagetop">
<h1 class="page-title"><?php the_title(); ?></h1>
<?php do_action('progo_pagetop'); ?>
</div>
<div id="main" role="main" class="grid_8">
<?php echo wpsc_display_products_page( array( 'category_url_name'=>'featured' ) ); ?>
</div><!-- #main -->
<?php endwhile; ?>
<?php get_sidebar(); ?>
</div><!-- #container -->
<?php get_footer(); ?>