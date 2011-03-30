<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package ProGo
 * @subpackage Ecommerce
 * @since Ecommerce 1.0
 */

get_header();
?>
<div id="container" class="container_12">
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
<div class="grid_12" id="pagetop">
<h1 class="page-title"><?php the_title(); ?></h1>
<?php do_action('progo_pagetop'); ?>
</div>
<div id="main" role="main" class="grid_8">
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<div class="entry">
<?php the_content(); ?>
</div><!-- .entry -->
</div><!-- #post-## -->
</div><!-- #main -->
<?php endwhile; ?>
<?php get_sidebar(); ?>
</div><!-- #container -->
<?php get_footer(); ?>
