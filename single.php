<?php
/**
 * The Template for displaying all single posts.
 *
 * @package ProGo
 * @subpackage Ecommerce
 * @since Ecommerce 1.0
 */

get_header(); ?>
<div id="container" class="container_12">
<div id="pagetop">
<h1 class="page-title"><?php echo get_the_title( get_option('progo_blog_id') ); ?></h1>
<?php do_action('progo_pagetop'); ?>
</div>
<div id="main" class="grid_8">
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<h2 class="entry-title"><?php the_title(); ?></h2>
<div class="entry-meta"><?php progo_posted_on(); ?></div>
<div class="entry">
<?php the_content(); ?>
</div><!-- .entry -->
<div class="entry-utility"><div class="in"><?php progo_posted_in(); ?></div><div class="sha"><?php if(function_exists('sharethis_button')) sharethis_button(); ?></div></div>
</div><!-- #post-## -->
<?php comments_template( '', true ); ?>
<?php endwhile; // end of the loop. ?>
</div><!-- #main -->
<?php get_sidebar('blog'); ?>
		</div><!-- #container -->
<?php get_footer(); ?>